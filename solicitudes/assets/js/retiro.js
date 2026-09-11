(() => {
  const form = document.querySelector('#retiro-form');
  if (!form) return;
  const status = form.querySelector('.form-status');
  const templates = { horario: document.querySelector('#horario-template'), residuo: document.querySelector('#residuo-template') };
  const lists = { horario: document.querySelector('#horarios'), residuo: document.querySelector('#residuos') };
  const indexes = { horario: 0, residuo: 0 };
  const add = (kind) => {
    const fragment = templates[kind].content.cloneNode(true);
    fragment.querySelectorAll('[name]').forEach((field) => { field.name = field.name.replace('__INDEX__', indexes[kind]); });
    indexes[kind] += 1;
    lists[kind].append(fragment);
    updateRemoveButtons(kind);
  };
  const updateRemoveButtons = (kind) => {
    lists[kind].querySelectorAll('.remove-item').forEach((button, index) => { button.disabled = index === 0; button.hidden = index === 0; });
  };
  add('horario'); add('residuo');
  document.querySelectorAll('[data-add]').forEach((button) => button.addEventListener('click', () => add(button.dataset.add)));
  form.addEventListener('click', (event) => {
    const button = event.target.closest('.remove-item');
    if (!button || button.disabled) return;
    const card = button.closest('.repeat-card');
    const kind = card.classList.contains('horario') ? 'horario' : 'residuo';
    card.remove(); updateRemoveButtons(kind); updateTotals();
  });
  const syncHys = () => {
    const same = form.elements.hys_misma_persona.checked;
    [['contacto_nombre', 'hys_nombre'], ['contacto_telefono', 'hys_telefono'], ['contacto_email', 'hys_email']].forEach(([source, target]) => {
      const input = form.elements[target]; input.disabled = same; input.required = !same;
      if (same) input.value = form.elements[source].value;
    });
  };
  form.elements.hys_misma_persona.addEventListener('change', syncHys);
  ['contacto_nombre', 'contacto_telefono', 'contacto_email'].forEach((name) => form.elements[name].addEventListener('input', syncHys));
  const conditions = () => {
    form.querySelectorAll('[data-when]').forEach((box) => {
      const [name, value] = box.dataset.when.split(':');
      const field = form.elements[name];
      const checked = field instanceof RadioNodeList ? field.value === value : field.value === value;
      box.hidden = !checked;
      box.querySelectorAll('input, select, textarea').forEach((input) => { input.disabled = !checked; });
    });
  };
  form.addEventListener('change', conditions); conditions();
  const updateCategory = (card) => {
    const select = card.querySelector('.rrpp-category'); const selected = select.selectedOptions[0];
    card.querySelector('.rrpp-description').textContent = selected?.dataset.description || '';
  };
  form.addEventListener('input', (event) => {
    const card = event.target.closest('.residuo');
    if (!card) return;
    if (event.target.matches('.rrpp-search')) {
      const search = event.target.value.trim().toLocaleLowerCase('es');
      card.querySelectorAll('.rrpp-category option').forEach((option, index) => { if (index) option.hidden = !!search && !option.text.toLocaleLowerCase('es').includes(search); });
    }
    updateTotals();
  });
  form.addEventListener('change', (event) => { const card = event.target.closest('.residuo'); if (card) { updateCategory(card); updateTotals(); } });
  const updateTotals = () => {
    const totals = { 'm³': 0, kg: 0, L: 0, tn: 0 };
    form.querySelectorAll('.residuo').forEach((card) => { const amount = Number.parseFloat(card.querySelector('.residuo-cantidad').value.replace(',', '.')); const unit = card.querySelector('.residuo-unidad').value; if (Number.isFinite(amount) && amount > 0 && Object.hasOwn(totals, unit)) totals[unit] += amount; });
    Object.entries(totals).forEach(([unit, total]) => { form.querySelector(`[data-total="${CSS.escape(unit)}"]`).textContent = `${total} ${unit}`; });
  };
  form.addEventListener('submit', async (event) => {
    event.preventDefault(); status.textContent = '';
    if (!form.checkValidity()) { form.reportValidity(); form.querySelector(':invalid')?.focus(); return; }
    const submit = form.querySelector('[type="submit"]'); submit.disabled = true; submit.textContent = 'Enviando solicitud…';
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const result = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(result.error || 'No se pudo enviar la solicitud. Intentá nuevamente.');
      form.replaceWith(Object.assign(document.createElement('section'), { className: 'result-card', innerHTML: `<h2>Solicitud recibida correctamente</h2><p>Número: <strong>${result.numero}</strong></p><p>${result.copia_enviada ? 'Te enviamos una copia por email.' : 'TeCMA recibió correctamente la solicitud, pero no pudo enviarse la confirmación por email.'}</p>${result.manifiesto_pendiente ? '<p class="warning">NO PROGRAMAR — PENDIENTE DE MANIFIESTO</p>' : ''}` }));
    } catch (error) { status.textContent = error.message; status.focus(); submit.disabled = false; submit.textContent = 'Enviar solicitud de retiro'; }
  });
})();
