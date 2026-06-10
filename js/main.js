document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-toggle-password]');
  const password = document.querySelector('#password');

  if (toggle && password) {
    toggle.addEventListener('click', () => {
      password.type = password.type === 'password' ? 'text' : 'password';
    });
  }

  document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
      if (!confirm(button.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });
});
