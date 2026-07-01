import './bootstrap';

const header = document.querySelector('.site-header');
const menuToggle = document.querySelector('[data-menu-toggle]');

menuToggle?.addEventListener('click', () => {
    const isOpen = header?.classList.toggle('is-open') ?? false;
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});

document.querySelectorAll('.site-nav a, .auth-links a').forEach((link) => {
    link.addEventListener('click', () => {
        header?.classList.remove('is-open');
        menuToggle?.setAttribute('aria-expanded', 'false');
    });
});

document.querySelectorAll('.faq-list details').forEach((detail) => {
    detail.addEventListener('toggle', () => {
        if (!detail.open) {
            return;
        }

        document.querySelectorAll('.faq-list details[open]').forEach((openDetail) => {
            if (openDetail !== detail) {
                openDetail.removeAttribute('open');
            }
        });
    });
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const input = toggle.closest('.password-field')?.querySelector('input');

    toggle.addEventListener('click', () => {
        if (!input) {
            return;
        }

        const shouldShow = input.type === 'password';
        input.type = shouldShow ? 'text' : 'password';
        toggle.classList.toggle('is-visible', shouldShow);
        toggle.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
    });
});
