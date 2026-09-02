(() => {
    'use strict';

    const settings = window.adsTourismListings || {};
    const requests = new Map();
    const timers = new Map();
    const observedInfiniteLinks = new WeakSet();

    const contextNodes = (context) => Array.from(document.querySelectorAll(
        `[data-ads-tourism-context="${context}"]`,
    ));

    const resultComponent = (context) => contextNodes(context).find(
        (node) => node.dataset.adsTourismComponent === 'results' && node.dataset.query,
    );

    const parameterPrefix = (context) => `ads_${context.toLowerCase()}_`;

    const queryConfiguration = (context) => {
        const component = resultComponent(context);

        if (!component) {
            return null;
        }

        try {
            return JSON.parse(component.dataset.query || '{}');
        } catch (error) {
            return null;
        }
    };

    const urlState = (context, configuration) => {
        const parameters = new URLSearchParams(window.location.search);
        const prefix = parameterPrefix(context);
        const state = {...configuration};
        state.taxonomies = {...(state.taxonomies || {})};
        state.relationships = {...(state.relationships || {})};

        parameters.forEach((value, name) => {
            if (!name.startsWith(prefix)) {
                return;
            }

            const key = name.slice(prefix.length);

            if (key === 'query') state.keyword = value;
            else if (key === 'sort') state.sort = value;
            else if (key === 'page') state.page = Math.max(1, Number.parseInt(value, 10) || 1);
            else if (key === 'minimum_price') state.minimum_price = value;
            else if (key === 'maximum_price') state.maximum_price = value;
            else if (key === 'minimum_duration') state.minimum_duration = value;
            else if (key === 'maximum_duration') state.maximum_duration = value;
            else if (key.startsWith('tax_')) state.taxonomies[key.slice(4)] = value ? [value] : [];
            else if (key.startsWith('rel_')) state.relationships[key.slice(4)] = value;
        });

        return state;
    };

    const controlState = (context, configuration) => {
        const state = urlState(context, configuration);
        const prefix = parameterPrefix(context);

        contextNodes(context).forEach((node) => {
            if (!(node instanceof HTMLFormElement)) return;

            new FormData(node).forEach((value, name) => {
                if (typeof value !== 'string' || !name.startsWith(prefix)) return;
                const key = name.slice(prefix.length);

                if (key === 'query') state.keyword = value;
                else if (key === 'sort') state.sort = value;
                else if (key.startsWith('tax_')) state.taxonomies[key.slice(4)] = value ? [value] : [];
                else if (key.startsWith('rel_')) {
                    if (value) state.relationships[key.slice(4)] = value;
                    else delete state.relationships[key.slice(4)];
                } else if (['minimum_price', 'maximum_price', 'minimum_duration', 'maximum_duration'].includes(key)) {
                    state[key] = value;
                }
            });
        });

        return state;
    };

    const updateUrl = (context, state) => {
        const url = new URL(window.location.href);
        const prefix = parameterPrefix(context);
        Array.from(url.searchParams.keys()).forEach((key) => {
            if (key.startsWith(prefix)) url.searchParams.delete(key);
        });

        const set = (key, value) => {
            if (value !== '' && value !== null && value !== undefined) {
                url.searchParams.set(`${prefix}${key}`, String(value));
            }
        };
        set('query', state.keyword);
        set('sort', state.sort);
        set('page', state.page > 1 ? state.page : '');
        set('minimum_price', state.minimum_price);
        set('maximum_price', state.maximum_price);
        set('minimum_duration', state.minimum_duration);
        set('maximum_duration', state.maximum_duration);
        Object.entries(state.taxonomies || {}).forEach(([taxonomy, terms]) => {
            set(`tax_${taxonomy}`, Array.isArray(terms) ? terms.join(',') : terms);
        });
        Object.entries(state.relationships || {}).forEach(([type, id]) => set(`rel_${type}`, id));
        window.history.pushState({}, '', url);
    };

    const requestParameters = (state, columns) => {
        const parameters = new URLSearchParams();
        parameters.set('context', state.context);
        parameters.set('type', (state.types || []).join(','));
        parameters.set('query', state.keyword || '');
        parameters.set('page', String(state.page || 1));
        parameters.set('per_page', String(state.per_page || 12));
        parameters.set('sort', state.sort || 'title_asc');
        parameters.set('pagination', state.pagination || 'numbered');
        parameters.set('columns', String(columns || 3));
        parameters.set('taxonomies', JSON.stringify(state.taxonomies || {}));
        parameters.set('relationships', JSON.stringify(state.relationships || {}));
        ['minimum_price', 'maximum_price', 'minimum_duration', 'maximum_duration'].forEach((key) => {
            if (state[key] !== '' && state[key] !== null && state[key] !== undefined) {
                parameters.set(key, String(state[key]));
            }
        });

        return parameters;
    };

    const announceError = (context) => {
        const liveRegion = contextNodes(context)
            .map((node) => node.querySelector('[aria-live]'))
            .find(Boolean);

        if (liveRegion) liveRegion.textContent = settings.errorMessage || 'Unable to update results.';
    };

    const syncControls = (context, state) => {
        const prefix = parameterPrefix(context);
        const values = {
            query: state.keyword || '',
            sort: state.sort || 'title_asc',
            minimum_price: state.minimum_price ?? '',
            maximum_price: state.maximum_price ?? '',
            minimum_duration: state.minimum_duration ?? '',
            maximum_duration: state.maximum_duration ?? '',
        };

        Object.entries(state.taxonomies || {}).forEach(([taxonomy, terms]) => {
            values[`tax_${taxonomy}`] = Array.isArray(terms) ? terms.join(',') : terms;
        });
        Object.entries(state.relationships || {}).forEach(([type, id]) => {
            values[`rel_${type}`] = id;
        });

        contextNodes(context).forEach((node) => {
            if (!(node instanceof HTMLFormElement)) return;

            node.querySelectorAll('[name]').forEach((control) => {
                if (!(control instanceof HTMLInputElement || control instanceof HTMLSelectElement)) return;
                if (!control.name.startsWith(prefix)) return;

                const key = control.name.slice(prefix.length);
                if (Object.prototype.hasOwnProperty.call(values, key)) {
                    control.value = String(values[key] ?? '');
                }
            });
        });
    };

    const infiniteObserver = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries) => {
            entries.filter((entry) => entry.isIntersecting).forEach((entry) => {
                const link = entry.target;
                const holder = link.closest('[data-ads-tourism-context]');
                const context = holder?.dataset.adsTourismContext;
                const configuration = context ? queryConfiguration(context) : null;

                if (!context || !configuration || configuration.pagination !== 'infinite') return;
                if (requests.has(context)) return;
                link.click();
            });
        }, {rootMargin: '200px'})
        : null;

    const observeInfinitePagination = (context) => {
        if (!infiniteObserver) return;

        contextNodes(context).forEach((node) => {
            const links = node.matches('[data-ads-tourism-pagination] a[data-page]')
                ? [node]
                : Array.from(node.querySelectorAll('[data-ads-tourism-pagination] a[data-page]'));

            links.forEach((link) => {
                if (!observedInfiniteLinks.has(link)) {
                    observedInfiniteLinks.add(link);
                    infiniteObserver.observe(link);
                }
            });
        });
    };

    const replaceResults = (context, response, append) => {
        const current = contextNodes(context)
            .map((node) => node.matches('[data-ads-tourism-results]') ? node : node.querySelector('[data-ads-tourism-results]'))
            .find(Boolean);
        const fragment = document.createRange().createContextualFragment(response.html || '');
        const replacement = fragment.querySelector('[data-ads-tourism-results]');

        if (!current || !replacement) return;

        if (append) {
            const currentGrid = current.querySelector('.ads-tourism-grid');
            const newGrid = replacement.querySelector('.ads-tourism-grid');

            if (currentGrid && newGrid) {
                Array.from(newGrid.children).forEach((card) => currentGrid.append(card));
                const status = current.querySelector('[aria-live]');
                const newStatus = replacement.querySelector('[aria-live]');
                if (status && newStatus) status.textContent = newStatus.textContent;
            } else {
                current.replaceWith(replacement);
            }
        } else {
            current.replaceWith(replacement);
        }

        const paginationContainers = new Set();
        contextNodes(context).forEach((node) => {
            if (node.hasAttribute('data-ads-tourism-pagination-container')) {
                paginationContainers.add(node);
            }

            node.querySelectorAll('[data-ads-tourism-pagination-container]')
                .forEach((container) => paginationContainers.add(container));
        });
        paginationContainers.forEach((container) => {
            container.innerHTML = response.pagination_html || '';
        });
        observeInfinitePagination(context);

        const updated = contextNodes(context)
            .map((node) => node.matches('[data-ads-tourism-results]') ? node : node.querySelector('[data-ads-tourism-results]'))
            .find(Boolean);
        if (updated && !append) updated.focus({preventScroll: false});
    };

    const load = async (context, state, updateHistory = true) => {
        const component = resultComponent(context);

        if (!component || !settings.endpoint) return;
        requests.get(context)?.abort();
        const controller = new AbortController();
        requests.set(context, controller);
        component.setAttribute('aria-busy', 'true');

        if (updateHistory) updateUrl(context, state);

        try {
            const parameters = requestParameters(state, component.dataset.columns);
            const response = await fetch(`${settings.endpoint}?${parameters}`, {
                headers: {'Accept': 'application/json'},
                signal: controller.signal,
            });

            if (!response.ok) throw new Error('Request failed');
            const payload = await response.json();
            const append = ['load_more', 'infinite'].includes(state.pagination) && Number(state.page) > 1;
            replaceResults(context, payload, append);
            document.dispatchEvent(new CustomEvent('ads-tourism:results-updated', {
                detail: {
                    context,
                    markers: payload.markers || [],
                    markers_all: payload.markers_all || [],
                    state: payload.state || state,
                },
            }));
            const refreshed = resultComponent(context);
            if (refreshed) refreshed.dataset.query = JSON.stringify(payload.state || state);
        } catch (error) {
            if (error.name !== 'AbortError') announceError(context);
        } finally {
            resultComponent(context)?.removeAttribute('aria-busy');
            if (requests.get(context) === controller) requests.delete(context);
        }
    };

    const scheduleSearch = (context) => {
        window.clearTimeout(timers.get(context));
        timers.set(context, window.setTimeout(() => {
            const configuration = queryConfiguration(context);
            if (!configuration) return;
            const state = controlState(context, configuration);
            state.page = 1;
            load(context, state);
        }, 300));
    };

    document.addEventListener('input', (event) => {
        if (!event.target.matches('[data-ads-tourism-search-input]')) return;
        const form = event.target.closest('[data-ads-tourism-context]');
        if (form) scheduleSearch(form.dataset.adsTourismContext);
    });

    document.addEventListener('change', (event) => {
        if (!event.target.matches('[data-ads-tourism-sort-select], [data-ads-tourism-filter]')) return;
        const form = event.target.closest('[data-ads-tourism-context]');
        const context = form?.dataset.adsTourismContext;
        const configuration = context ? queryConfiguration(context) : null;
        if (!context || !configuration) return;
        const state = controlState(context, configuration);
        state.page = 1;
        load(context, state);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-ads-tourism-context]');
        if (!form) return;
        const context = form.dataset.adsTourismContext;
        const configuration = queryConfiguration(context);
        if (!configuration) return;
        event.preventDefault();
        const state = controlState(context, configuration);
        state.page = 1;
        load(context, state);
    });

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-ads-tourism-pagination] a[data-page]');
        if (!link) return;
        const holder = link.closest('[data-ads-tourism-context]');
        const context = holder?.dataset.adsTourismContext;
        const configuration = context ? queryConfiguration(context) : null;
        if (!context || !configuration) return;
        event.preventDefault();
        const state = controlState(context, configuration);
        state.page = Math.max(1, Number.parseInt(link.dataset.page, 10) || 1);
        load(context, state);
    });

    window.addEventListener('popstate', () => {
        const contexts = new Set(Array.from(document.querySelectorAll('[data-ads-tourism-context]'))
            .map((node) => node.dataset.adsTourismContext).filter(Boolean));
        contexts.forEach((context) => {
            const configuration = queryConfiguration(context);
            if (!configuration) return;

            const state = urlState(context, configuration);
            syncControls(context, state);
            load(context, state, false);
        });
    });

    new Set(Array.from(document.querySelectorAll('[data-ads-tourism-context]'))
        .map((node) => node.dataset.adsTourismContext).filter(Boolean))
        .forEach((context) => observeInfinitePagination(context));
})();
