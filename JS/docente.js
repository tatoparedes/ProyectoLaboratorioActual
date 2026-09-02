document.addEventListener("DOMContentLoaded", function () {
    // ---- Lógica para la navegación móvil (hamburguesa) ----
    const hamburger = document.getElementById("hamburger");
    const navMenu = document.getElementById("nav-menu");

    if (hamburger && navMenu) {
        hamburger.addEventListener("click", () => {
            navMenu.classList.toggle("active");
        });
    }

    // ---- Lógica para la navegación de la barra lateral ----
    const sidebarButtons = document.querySelectorAll(".sidebar-btn");
    const contentPanels = document.querySelectorAll(".content-panel");

    // Activar panel según el hash de la URL (ej. #panel-familias) al cargar la página
    function activarPanelPorHash() {
        const hash = window.location.hash;
        if (hash) {
            const targetPanel = document.querySelector(hash);
            if (targetPanel) {
                sidebarButtons.forEach(btn => btn.classList.remove("active"));
                contentPanels.forEach(panel => panel.classList.remove("active"));

                targetPanel.classList.add("active");

                const targetBtn = document.querySelector(`.sidebar-btn[href="${hash}"], .sidebar-btn[href="vista_docente.php${hash}"]`);
                if (targetBtn) {
                    targetBtn.classList.add("active");
                }
            }
        }
    }

    activarPanelPorHash();
    window.addEventListener("hashchange", activarPanelPorHash);

    sidebarButtons.forEach(button => {
        button.addEventListener("click", (event) => {
            const targetId = button.getAttribute("href");
            if (targetId && !targetId.startsWith('#')) {
                return; // Permitir navegación a otros archivos php
            }
            event.preventDefault();

            if (navMenu && navMenu.classList.contains("active")) {
                navMenu.classList.remove("active");
            }

            sidebarButtons.forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");

            contentPanels.forEach(panel => panel.classList.remove("active"));

            const targetPanel = document.querySelector(targetId);
            if (targetPanel) {
                targetPanel.classList.add("active");
            }
        });
    });
});