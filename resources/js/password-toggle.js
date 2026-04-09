const observedInputs = new WeakMap();

function sync(input, btn) {
  const isPassword = input.type === 'password';

  const eyeOpen = btn.querySelector('.eye-open');
  const eyeClosed = btn.querySelector('.eye-closed');

  if (isPassword) {
    eyeOpen?.classList.remove('hidden');
    eyeClosed?.classList.add('hidden');
    btn.setAttribute('aria-pressed', 'false');
  } else {
    eyeOpen?.classList.add('hidden');
    eyeClosed?.classList.remove('hidden');
    btn.setAttribute('aria-pressed', 'true');
  }
}

function ensureObserver(input, btn) {
  if (observedInputs.has(input)) return;

  const observer = new MutationObserver(() => {
    document
      .querySelectorAll(`[data-toggle-password="${input.id}"]`)
      .forEach(btn => sync(input, btn));
  });

  observer.observe(input, {
    attributes: true,
    attributeFilter: ['type']
  });

  observedInputs.set(input, observer);
}

document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-toggle-password]');
  if (!btn) return;

  e.preventDefault();

  const input = document.getElementById(btn.dataset.togglePassword);
  if (!input) return;

  ensureObserver(input, btn);

  input.type = input.type === 'password' ? 'text' : 'password';

  sync(input, btn);

  try { input.focus({ preventScroll: true }); } catch {}
});

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    const input = document.getElementById(btn.dataset.togglePassword);
    if (!input) return;

    ensureObserver(input, btn);
    sync(input, btn);
  });
});