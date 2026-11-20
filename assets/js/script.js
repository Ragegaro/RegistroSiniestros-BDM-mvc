/** Interacciones globales. La aplicación conserva su función sin JavaScript. */
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.nav-toggle');
    const navigation = document.querySelector('.Navbar');
    if (!toggle || !navigation) return;

    toggle.addEventListener('click', function () {
        const isOpen = navigation.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.textContent = isOpen ? '✕ Cerrar menú' : '☰ Menú';
    });
});
