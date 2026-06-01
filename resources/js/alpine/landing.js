/**
 * Alpine component for the public landing page.
 * Handles: mobile nav, billing toggle, dark-mode toggle.
 *
 * @author BenjaminDTS
 */
export function registerLanding() {
    Alpine.data('landing', () => ({
        mobileOpen: false,
        billing: 'monthly',
        isDark: document.documentElement.classList.contains('dark'),

        toggleTheme() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
    }));
}
