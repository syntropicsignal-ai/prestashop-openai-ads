document.addEventListener('click', async function (event) {
  var button = event.target.closest('.oai-feed [data-oai-copy]');
  if (!button) return;
  var input = document.getElementById(button.dataset.oaiCopy);
  var status = button.closest('.oai-feed').querySelector('.oai-copy-status');
  try {
    await navigator.clipboard.writeText(input.value);
    status.textContent = button.dataset.copied;
  } catch (error) {
    input.focus();
    input.select();
    status.textContent = button.dataset.copyFailed;
  }
});

document.addEventListener('submit', function (event) {
  var form = event.target;
  if (!form.matches('.oai-feed .oai-sync-form')) return;
  var button = form.querySelector('button[type="submit"]');
  if (button.getAttribute('aria-disabled') === 'true') {
    event.preventDefault();
    return;
  }
  button.setAttribute('aria-disabled', 'true');
  button.textContent = form.dataset.processing;
});
