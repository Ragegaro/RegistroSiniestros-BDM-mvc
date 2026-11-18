/** Interacciones globales. La aplicación conserva su función sin JavaScript. */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.notice-close').forEach(function (button) {
        button.addEventListener('click', function () {
            const notice = button.closest('.app-notice');
            if (notice) notice.remove();
        });
    });
    const toggle = document.querySelector('.nav-toggle');
    const navigation = document.querySelector('.Navbar');
    if (!toggle || !navigation) return;

    toggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.textContent = isOpen ? '✕ Cerrar menú' : '☰ Menú';
    });
});
