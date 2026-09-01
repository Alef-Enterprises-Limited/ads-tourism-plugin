(() => {
  'use strict';

  const config = window.adsTourismTransfer;
  const uploadForm = document.getElementById('ads-tourism-import-upload');

  if (!config || !uploadForm) {
    return;
  }

  const configPanel = document.getElementById('ads-tourism-import-config');
  const mappingBody = document.getElementById('ads-tourism-mapping');
  const summary = document.getElementById('ads-tourism-import-summary');
  const previewButton = document.getElementById('ads-tourism-preview');
  const startButton = document.getElementById('ads-tourism-start');
  const previewResults = document.getElementById('ads-tourism-preview-results');
  const progress = document.getElementById('ads-tourism-import-progress');
  let runId = 0;

  const request = async (data) => {
    const response = await fetch(config.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: data,
    });
    const payload = await response.json();

    if (!response.ok || !payload.success) {
      throw new Error(payload.data?.message || config.messages.requestFailed);
    }

    return payload.data;
  };

  const baseData = (action) => {
    const data = new FormData();
    data.append('action', action);
    data.append('nonce', config.nonce);
    return data;
  };

  const mapping = () => {
    const values = {};
    mappingBody.querySelectorAll('select[data-source]').forEach((select) => {
      values[select.dataset.source] = select.value;
    });
    return values;
  };

  const setMessage = (element, message, error = false) => {
    element.textContent = message;
    element.classList.toggle('notice-error', error);
    element.classList.toggle('notice-success', !error);
    element.classList.add('notice', 'inline');
  };

  uploadForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    startButton.disabled = true;
    setMessage(progress, config.messages.working);
    const data = new FormData(uploadForm);
    data.append('action', config.actions.upload);
    data.append('nonce', config.nonce);

    try {
      const result = await request(data);
      runId = result.run_id;
      summary.textContent = `${result.total_rows} data rows; detected delimiter: ${result.delimiter}`;
      mappingBody.replaceChildren();

      result.headers.forEach((header) => {
        const row = document.createElement('tr');
        const sourceCell = document.createElement('td');
        const targetCell = document.createElement('td');
        const select = document.createElement('select');
        sourceCell.textContent = header;
        select.dataset.source = header;
        select.append(new Option('— Ignore this column —', ''));

        Object.entries(result.columns).forEach(([value, label]) => {
          select.append(new Option(`${label} (${value})`, value, false, result.mapping[header] === value));
        });

        targetCell.append(select);
        row.append(sourceCell, targetCell);
        mappingBody.append(row);
      });

      configPanel.hidden = false;
      progress.replaceChildren();
    } catch (error) {
      setMessage(progress, error.message, true);
    }
  });

  previewButton.addEventListener('click', async () => {
    startButton.disabled = true;
    setMessage(previewResults, config.messages.working);
    const data = baseData(config.actions.preview);
    data.append('run_id', String(runId));
    data.append('mapping', JSON.stringify(mapping()));
    data.append('duplicate_policy', document.getElementById('ads-tourism-duplicate-policy').value);
    data.append('taxonomy_mode', document.getElementById('ads-tourism-taxonomy-mode').value);
    data.append('allow_term_creation', document.getElementById('ads-tourism-create-terms')?.checked ? '1' : '0');

    try {
      const result = await request(data);
      previewResults.replaceChildren();
      const heading = document.createElement('h3');
      heading.textContent = 'Dry-run preview';
      const description = document.createElement('p');
      description.textContent = `${result.sample_valid_rows} valid and ${result.sample_invalid_rows} invalid rows in the ${result.sample_size}-row sample.`;
      const table = document.createElement('table');
      table.className = 'widefat striped';
      table.innerHTML = '<thead><tr><th>Row</th><th>External ID</th><th>Title</th><th>Validation</th></tr></thead>';
      const body = document.createElement('tbody');

      result.rows.forEach((row) => {
        const tr = document.createElement('tr');
        [row.row_number, row.external_id, row.title, [...row.errors, ...row.warnings].join(' | ') || 'Valid'].forEach((value) => {
          const td = document.createElement('td');
          td.textContent = value;
          tr.append(td);
        });
        body.append(tr);
      });

      table.append(body);
      previewResults.append(heading, description, table);
      startButton.disabled = false;
    } catch (error) {
      setMessage(previewResults, error.message, true);
    }
  });

  startButton.addEventListener('click', async () => {
    startButton.disabled = true;
    previewButton.disabled = true;

    try {
      let done = false;

      while (!done) {
        const data = baseData(config.actions.batch);
        data.append('run_id', String(runId));
        const result = await request(data);
        done = result.done;
        setMessage(
          progress,
          `${result.processed_rows}/${result.total_rows} processed — ${result.imported_rows} created, ${result.updated_rows} updated, ${result.skipped_rows} skipped, ${result.rejected_rows} rejected.`,
        );
      }

      setMessage(progress, config.messages.complete);
    } catch (error) {
      setMessage(progress, error.message, true);
      startButton.disabled = false;
    } finally {
      previewButton.disabled = false;
    }
  });
})();
