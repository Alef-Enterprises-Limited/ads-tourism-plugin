(() => {
    'use strict';

    const settings = window.adsTourismRelationships;

    if (!settings) {
        return;
    }

    const createButton = (className, label) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = label;

        return button;
    };

    const createSelectedItem = (control, item) => {
        const relationKey = control.dataset.relationKey;
        const listItem = document.createElement('li');
        listItem.dataset.postId = String(item.id);

        const title = document.createElement('span');
        title.className = 'ads-tourism-relation__title';
        title.textContent = item.title;
        listItem.append(title);

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `ads_tourism_relationships[${relationKey}][]`;
        hidden.value = String(item.id);
        listItem.append(hidden);

        if (control.dataset.allowsPrimary === '1') {
            const label = document.createElement('label');
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = `ads_tourism_primary[${relationKey}]`;
            radio.value = String(item.id);
            label.append(radio, ` ${settings.strings.primary}`);
            listItem.append(label);
        }

        const actions = document.createElement('span');
        actions.className = 'ads-tourism-relation__actions';
        actions.append(
            createButton('button-link ads-tourism-relation__up', settings.strings.moveUp),
            document.createTextNode(' '),
            createButton('button-link ads-tourism-relation__down', settings.strings.moveDown),
            document.createTextNode(' '),
            createButton('button-link-delete ads-tourism-relation__remove', settings.strings.remove),
        );
        listItem.append(actions);

        return listItem;
    };

    const renderResults = (control, items) => {
        const results = control.querySelector('.ads-tourism-relation__results');
        const selected = control.querySelector('.ads-tourism-relation__selected');
        results.replaceChildren();

        const selectedIds = new Set(
            Array.from(selected.querySelectorAll('li')).map((item) => item.dataset.postId),
        );

        if (items.length === 0) {
            const empty = document.createElement('li');
            empty.textContent = settings.strings.noResults;
            results.append(empty);
            return;
        }

        items.forEach((item) => {
            if (selectedIds.has(String(item.id))) {
                return;
            }

            const result = document.createElement('li');
            const add = createButton('button-link', `${item.title} (${item.post_type})`);
            add.addEventListener('click', () => {
                selected.append(createSelectedItem(control, item));
                result.remove();
            });
            result.append(add);
            results.append(result);
        });
    };

    const search = async (control, searchTerm) => {
        const query = new URLSearchParams({
            action: settings.action,
            nonce: settings.nonce,
            post_id: control.dataset.postId,
            relation_key: control.dataset.relationKey,
            search: searchTerm,
            page: '1',
        });

        try {
            const response = await fetch(`${settings.ajaxUrl}?${query.toString()}`, {
                credentials: 'same-origin',
            });
            const payload = await response.json();

            if (!response.ok || !payload.success) {
                throw new Error('Relationship search failed.');
            }

            renderResults(control, payload.data.items || []);
        } catch {
            const results = control.querySelector('.ads-tourism-relation__results');
            const error = document.createElement('li');
            error.textContent = settings.strings.searchFailed;
            results.replaceChildren(error);
        }
    };

    document.querySelectorAll('.ads-tourism-relation').forEach((control) => {
        const input = control.querySelector('.ads-tourism-relation__search');
        const selected = control.querySelector('.ads-tourism-relation__selected');
        let timer;

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => search(control, input.value.trim()), 250);
        });

        selected.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            const item = event.target.closest('li');

            if (!button || !item) {
                return;
            }

            if (button.classList.contains('ads-tourism-relation__remove')) {
                item.remove();
            } else if (button.classList.contains('ads-tourism-relation__up') && item.previousElementSibling) {
                selected.insertBefore(item, item.previousElementSibling);
            } else if (button.classList.contains('ads-tourism-relation__down') && item.nextElementSibling) {
                selected.insertBefore(item.nextElementSibling, item);
            }
        });
    });
})();
