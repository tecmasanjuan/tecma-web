(() => {
  const form = document.querySelector('.contact-form');
  const status = document.getElementById('form-status');
  const submitButton = form?.querySelector('button[type="submit"]');

  if (!form || !status || !submitButton) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (submitButton.disabled) return;

    submitButton.disabled = true;
    status.textContent = 'Enviando…';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });
      const result = await response.json().catch(() => ({}));

      if (!response.ok || !result.ok) {
        throw new Error(result.message || 'No pudimos enviar la consulta. Intentá nuevamente.');
      }

      form.reset();
      status.textContent = result.message || 'Recibimos tu consulta. Te responderemos a la brevedad.';
    } catch (error) {
      status.textContent = error.message || 'No pudimos enviar la consulta. Intentá nuevamente.';
    } finally {
      submitButton.disabled = false;
    }
  });
})();
