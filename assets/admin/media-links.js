(function () {
    'use strict';

    const list = document.querySelector('.ads-tourism-media__list');

    if (!list || typeof adsTourismMediaLinks === 'undefined') {
        return;
    }

    const roles = adsTourismMediaLinks.roles;
    const strings = adsTourismMediaLinks.strings;
    const chooseButton = document.querySelector('.ads-tourism-media__choose');
    const addUrlButton = document.querySelector('.ads-tourism-media__add-url');
    const urlInput = document.querySelector('#ads-tourism-media-url');
    const urlError = document.querySelector('.ads-tourism-media__url-error');

    function nextIndex() {
        return list.children.length;
    }

    function fieldName(index, field) {
        return `ads_tourism_media_links[${index}][${field}]`;
    }

    function input(type, field, value, index) {
        const element = document.createElement('input');
        element.type = type;
        element.dataset.field = field;
        element.name = fieldName(index, field);
        element.value = value;
        return element;
    }

    function labelledInput(labelText, field, value, index) {
        const label = document.createElement('label');
        label.append(document.createTextNode(`${labelText} `));
        const element = input('text', field, value, index);
        element.className = 'regular-text';
        label.append(element);
        return label;
    }

    function roleSelect(index) {
        const label = document.createElement('label');
        label.append(document.createTextNode(`${strings.role} `));
        const select = document.createElement('select');
        select.dataset.field = 'media_role';
        select.name = fieldName(index, 'media_role');

        Object.entries(roles).forEach(([value, text]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            option.selected = value === 'gallery';
            select.append(option);
        });

        label.append(select);
        return label;
    }

    function actionButton(className, text) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = text;
        return button;
    }

    function addItem(source) {
        const index = nextIndex();
        const item = document.createElement('li');
        item.className = 'ads-tourism-media__item';
        const preview = document.createElement('div');
        preview.className = 'ads-tourism-media__preview';
        const image = document.createElement('img');
        image.src = source.previewUrl;
        image.alt = '';
        preview.append(image);

        const fields = document.createElement('div');
        fields.className = 'ads-tourism-media__fields';
        fields.append(input('hidden', 'attachment_id', source.attachmentId || '0', index));
        fields.append(input('hidden', 'media_url', source.mediaUrl || '', index));
        fields.append(input('hidden', 'url_type', source.urlType || '', index));
        fields.append(roleSelect(index));
        fields.append(labelledInput(strings.title, 'custom_title', source.title || '', index));
        fields.append(labelledInput(strings.alt, 'custom_alt_text', source.alt || '', index));
        fields.append(labelledInput(strings.caption, 'custom_caption', source.caption || '', index));
        fields.append(labelledInput(strings.credit, 'credit', '', index));
        fields.append(labelledInput(strings.rights, 'rights_notice', '', index));

        const primaryLabel = document.createElement('label');
        const primary = document.createElement('input');
        primary.type = 'radio';
        primary.dataset.field = 'is_primary';
        primary.name = 'ads_tourism_media_primary';
        primary.value = String(index);
        primaryLabel.append(primary, document.createTextNode(` ${strings.primary}`));
        fields.append(primaryLabel);

        const actions = document.createElement('div');
        actions.className = 'ads-tourism-media__actions';
        actions.append(actionButton('button-link ads-tourism-media__up', strings.moveUp));
        actions.append(document.createTextNode(' '));
        actions.append(actionButton('button-link ads-tourism-media__down', strings.moveDown));
        actions.append(document.createTextNode(' '));
        actions.append(actionButton('button-link-delete ads-tourism-media__remove', strings.remove));
        item.append(preview, fields, actions);
        list.append(item);
        renumber();
    }

    function renumber() {
        Array.from(list.children).forEach((item, index) => {
            item.querySelectorAll('[data-field]').forEach((field) => {
                if (field.dataset.field === 'is_primary') {
                    field.value = String(index);
                    return;
                }

                field.name = fieldName(index, field.dataset.field);
            });
        });
    }

    chooseButton?.addEventListener('click', () => {
        const frame = wp.media({
            title: strings.chooseImages,
            button: { text: strings.useImages },
            library: { type: 'image' },
            multiple: true,
        });

        frame.on('select', () => {
            frame.state().get('selection').toJSON().forEach((attachment) => {
                const thumbnail = attachment.sizes?.thumbnail?.url || attachment.url;
                addItem({
                    attachmentId: String(attachment.id),
                    previewUrl: thumbnail,
                    title: attachment.title || '',
                    alt: attachment.alt || '',
                    caption: attachment.caption || '',
                });
            });
        });
        frame.open();
    });

    addUrlButton?.addEventListener('click', () => {
        const value = urlInput.value.trim();
        const relative = value.startsWith('/') && !value.startsWith('//');
        let absolute = false;

        try {
            absolute = new URL(value).protocol === 'https:';
        } catch (error) {
            absolute = false;
        }

        if (!relative && !absolute) {
            urlError.textContent = strings.invalidUrl;
            return;
        }

        urlError.textContent = '';
        addItem({
            mediaUrl: value,
            urlType: relative ? 'relative' : 'absolute',
            previewUrl: value,
        });
        urlInput.value = '';
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('button');
        const item = event.target.closest('.ads-tourism-media__item');

        if (!button || !item) {
            return;
        }

        if (button.classList.contains('ads-tourism-media__remove')) {
            item.remove();
        } else if (button.classList.contains('ads-tourism-media__up') && item.previousElementSibling) {
            list.insertBefore(item, item.previousElementSibling);
        } else if (button.classList.contains('ads-tourism-media__down') && item.nextElementSibling) {
            list.insertBefore(item.nextElementSibling, item);
        }

        renumber();
    });
})();
