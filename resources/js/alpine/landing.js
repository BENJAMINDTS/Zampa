/**
 * Alpine components for the public landing page.
 * Handles: mobile nav, billing toggle, dark-mode toggle, scroll navbar,
 *          scroll-reveal IntersectionObserver, and stats counter animation.
 *
 * @author BenjaminDTS
 */
export function registerLanding() {

    Alpine.data('landing', () => ({
        mobileOpen: false,
        billing: 'monthly',
        isDark: document.documentElement.classList.contains('dark'),
        scrolled: false,

        init() {
            // Navbar transparency on scroll
            const onScroll = () => { this.scrolled = window.scrollY > 24; };
            window.addEventListener('scroll', onScroll, { passive: true });

            // Scroll reveal — observe every .reveal element
            const revealObs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('is-visible');
                        revealObs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.10, rootMargin: '0px 0px -48px 0px' });

            document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

            this.$cleanup = () => {
                window.removeEventListener('scroll', onScroll);
                revealObs.disconnect();
            };
        },

        toggleTheme() {
            this.isDark = !this.isDark;
            document.documentElement.classList.toggle('dark', this.isDark);
            localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
        },
    }));

    // Stats counter — animates data-count-to numbers when section enters viewport
    Alpine.data('statsCounter', () => ({
        animated: false,

        init() {
            const obs = new IntersectionObserver(([entry]) => {
                if (entry.isIntersecting && !this.animated) {
                    this.animated = true;
                    this._run();
                    obs.disconnect();
                }
            }, { threshold: 0.5 });
            obs.observe(this.$el);
        },

        _run() {
            this.$el.querySelectorAll('[data-count-to]').forEach(el => {
                const to       = parseFloat(el.dataset.countTo);
                const prefix   = el.dataset.prefix  ?? '';
                const suffix   = el.dataset.suffix  ?? '';
                const decimals = parseInt(el.dataset.decimals ?? '0', 10);
                const duration = 1800;
                const start    = performance.now();

                const tick = (now) => {
                    const p      = Math.min((now - start) / duration, 1);
                    const eased  = 1 - Math.pow(1 - p, 3);
                    el.textContent = prefix + (to * eased).toFixed(decimals) + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        },
    }));
}
