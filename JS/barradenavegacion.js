document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Cerrar menú al hacer clic en un enlace
        navMenu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Cerrar al hacer clic fuera del menú
        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !hamburger.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }

    // Modal banner afiche informativo
    const modal = document.getElementById("modal-banner-1");
    if (modal) {
        modal.style.display = "flex";

        // Cerrar al hacer clic en el fondo oscuro
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Cerrar al presionar Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') {
                closeModal();
            }
        });
    }
});

// Función para cerrar modal
function closeModal() {
    const modal = document.getElementById("modal-banner-1");
    if (modal) {
        modal.style.display = "none";
    }
}

// Función para reabrir modal
function openModalAfiche() {
    const modal = document.getElementById("modal-banner-1");
    if (modal) {
        modal.style.display = "flex";
    }
}
