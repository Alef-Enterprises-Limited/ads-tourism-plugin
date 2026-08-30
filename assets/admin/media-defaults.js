(function () {
    'use strict';

    if (typeof adsTourismMediaDefaults === 'undefined') {
        return;
    }

    document.querySelectorAll('.ads-tourism-default-image').forEach((container) => {
        const field = container.querySelector('[data-attachment-id]');
        const preview = container.querySelector('[data-preview]');
        const choose = container.querySelector('[data-choose-default-image]');
        const clear = container.querySelector('[data-clear-default-image]');

        choose?.addEventListener('click', () => {
            const frame = wp.media({
                title: adsTourismMediaDefaults.title,
                button: { text: adsTourismMediaDefaults.button },
                library: { type: 'image' },
                multiple: false,
            });

            frame.on('select', () => {
                const attachment = frame.state().get('selection').first().toJSON();
                field.value = String(attachment.id);
                preview.src = attachment.sizes?.thumbnail?.url || attachment.url;
                preview.style.display = 'block';
            });
            frame.open();
        });

        clear?.addEventListener('click', () => {
            field.value = '0';
            preview.src = '';
            preview.style.display = 'none';
        });
    });
})();
