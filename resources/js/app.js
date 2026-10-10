import Alpine from 'alpinejs';

window.Alpine = Alpine;

// State layout aplikasi (sidebar mobile), dipakai di layouts/app.blade.php.
Alpine.data('appShell', () => ({
    sidebarOpen: false,
    isDesktop: window.matchMedia('(min-width: 1024px)').matches,

    init() {
        window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
            this.isDesktop = event.matches;

            if (event.matches) {
                this.sidebarOpen = false;
            }
        });
    },

    openSidebar() {
        this.sidebarOpen = true;
        this.$nextTick(() => this.$refs.sidebarClose?.focus());
    },

    closeSidebar() {
        if (! this.sidebarOpen) {
            return;
        }

        this.sidebarOpen = false;
        this.$nextTick(() => this.$refs.sidebarToggle?.focus());
    },
}));

Alpine.start();
