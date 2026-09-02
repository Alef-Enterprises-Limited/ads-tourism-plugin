(() => {
  'use strict';

  const config = window.adsTourismLocations;
  const list = document.querySelector('.ads-tourism-locations__list');
  const addButton = document.querySelector('.ads-tourism-locations__add');

  if (!config || !list || !addButton) {
    return;
  }

  const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

  const roleOptions = Object.entries(config.roles || {})
    .map(([value, label]) => `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`)
    .join('');

  const addRow = () => {
    const index = Number.parseInt(list.dataset.nextIndex || '0', 10);
    list.dataset.nextIndex = String(index + 1);
    const prefix = `ads_tourism_locations[${index}]`;
    const item = document.createElement('li');
    item.className = 'ads-tourism-locations__item';
    item.innerHTML = `
      <div class="ads-tourism-locations__fields">
        <label>${escapeHtml(config.strings.label)} <input class="regular-text" type="text" name="${prefix}[label]"></label>
        <label>${escapeHtml(config.strings.role)} <select name="${prefix}[role]">${roleOptions}</select></label>
        <label>${escapeHtml(config.strings.latitude)} <input class="regular-text" type="number" step="any" name="${prefix}[latitude]"></label>
        <label>${escapeHtml(config.strings.longitude)} <input class="regular-text" type="number" step="any" name="${prefix}[longitude]"></label>
        <label>${escapeHtml(config.strings.sortOrder)} <input class="regular-text" type="number" min="0" step="1" name="${prefix}[sort_order]" value="${index}"></label>
        <label><input type="checkbox" name="${prefix}[is_primary]" value="1"> ${escapeHtml(config.strings.primary)}</label>
        <label><input type="checkbox" name="${prefix}[show_on_map]" value="1" checked> ${escapeHtml(config.strings.showOnMap)}</label>
      </div>
      <button type="button" class="button-link-delete ads-tourism-locations__remove">${escapeHtml(config.strings.remove)}</button>`;
    list.append(item);
  };

  list.dataset.nextIndex = String(list.querySelectorAll('.ads-tourism-locations__item').length);
  addButton.addEventListener('click', addRow);
  list.addEventListener('click', (event) => {
    if (event.target instanceof HTMLElement && event.target.matches('.ads-tourism-locations__remove')) {
      event.target.closest('.ads-tourism-locations__item')?.remove();
    }
  });
})();
