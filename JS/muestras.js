    document.addEventListener('DOMContentLoaded', function () {
        const menuItems = document.querySelectorAll('.menu-item');
        const submenuWrapper = document.querySelector('.submenu-wrapper');
        const categoriesContainer = document.querySelector('.menu-categories');

        // Function to determine if we are in desktop view
        const isDesktop = () => window.innerWidth >= 992;

        // Function to setup the desktop layout
        function setupDesktopLayout() {
            // Move all submenus to the right wrapper
            menuItems.forEach(item => {
                const submenu = item.querySelector('.submenu-content');
                if (submenu) {
                    submenuWrapper.appendChild(submenu);
                }
            });

            // Set the first item as active by default
            if (menuItems.length > 0) {
                const firstItem = menuItems[0];
                const firstSubmenuId = firstItem.querySelector('.submenu-content').id;
                
                // Use the submenu itself for activation logic
                const firstSubmenu = submenuWrapper.querySelector('.submenu-content');

                firstItem.classList.add('active');
                if(firstSubmenu) firstSubmenu.classList.add('active');
            }
        }
        
        // Function to setup the mobile (accordion) layout
        function setupMobileLayout() {
            // Move submenus back to their respective category items
            menuItems.forEach(item => {
                const submenu = submenuWrapper.querySelector('.submenu-content');
                if(submenu){
                    item.appendChild(submenu);
                }
            });
            // Set the first item as active by default on mobile too
            if (menuItems.length > 0) {
                menuItems[0].classList.add('active');
            }
        }

        // Main logic to handle clicks
        categoriesContainer.addEventListener('click', (e) => {
            const trigger = e.target.closest('.category-trigger');
            if (!trigger) return;

            const parentItem = trigger.parentElement;

            // Find the associated submenu
            let targetSubmenu;
            if(isDesktop()){
                // On desktop, the submenu is in the wrapper. We need to find it.
                // Let's give each submenu a unique ID based on its content for robust matching
                const categoryText = trigger.querySelector('span').textContent.trim();
                // This is a simplified matching. For a real app, use data-attributes.
                // For now, we'll just use the index.
                const itemIndex = Array.from(menuItems).indexOf(parentItem);
                targetSubmenu = submenuWrapper.querySelectorAll('.submenu-content')[itemIndex];

            } else {
                // On mobile, the submenu is a direct sibling
                targetSubmenu = trigger.nextElementSibling;
            }
            
            // If the clicked item is already active (on mobile), close it.
            if (parentItem.classList.contains('active') && !isDesktop()) {
                parentItem.classList.remove('active');
            } else {
                 // Remove active class from all items and submenus
                menuItems.forEach(item => item.classList.remove('active'));
                document.querySelectorAll('.submenu-content').forEach(sub => sub.classList.remove('active'));

                // Add active class to the clicked item and its submenu
                parentItem.classList.add('active');
                if(targetSubmenu) targetSubmenu.classList.add('active');
            }
        });

        // Initial setup based on window size
        let wasDesktop = isDesktop();
        if (wasDesktop) {
            setupDesktopLayout();
        } else {
            // No need to call mobile setup on load, it's the default HTML structure
            if (menuItems.length > 0) {
                 menuItems[0].classList.add('active');
            }
        }

        // Listen for window resize to switch between layouts
        window.addEventListener('resize', () => {
            const currentlyDesktop = isDesktop();
            if (currentlyDesktop && !wasDesktop) {
                // Switched from mobile to desktop
                setupDesktopLayout();
            } else if (!currentlyDesktop && wasDesktop) {
                // Switched from desktop to mobile
                setupMobileLayout();
            }
            wasDesktop = currentlyDesktop;
        });
    });
