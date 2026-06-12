/**
 * Dispara las animaciones is-bouncing-top / is-bouncing-bottom
 * cuando el usuario intenta hacer scroll más allá del límite de un contenedor.
 *
 * @author Ayrton
 */

const BOUNCE_SELECTORS = [
    '.carta__body',
    '.carta__filters',
    '.cart-drawer__body',
    '.drawer__body',
    '.chat-log',
    '.pdetail__scroll',
    '.orders-body',
    '.variant-picker__list',
    '.dm-modal__body',
    '.tapas-list',
    '.tapas-body',
    '.split-items__list',
].join(', ');

/**
 * @param {Element} el
 */
function attachBounce(el) {
    let touchStartY = 0;
    let bouncing    = false;

    const atTop    = () => el.scrollTop <= 0;
    const atBottom = () => el.scrollTop + el.clientHeight >= el.scrollHeight - 1;

    function tryBounce(dir) {
        if (bouncing) return;
        bouncing     = true;
        const cls    = dir === 'top' ? 'is-bouncing-top' : 'is-bouncing-bottom';
        el.classList.remove('is-bouncing-top', 'is-bouncing-bottom');
        void el.offsetWidth;
        el.classList.add(cls);
        el.addEventListener('animationend', () => {
            el.classList.remove(cls);
            bouncing = false;
        }, { once: true });
    }

    el.addEventListener('wheel', (e) => {
        if (e.deltaY < 0 && atTop())    tryBounce('top');
        else if (e.deltaY > 0 && atBottom()) tryBounce('bottom');
    }, { passive: true });

    el.addEventListener('touchstart', (e) => {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });

    el.addEventListener('touchmove', (e) => {
        const dy = touchStartY - e.touches[0].clientY;
        if (dy < -8 && atTop())    tryBounce('top');
        else if (dy > 8 && atBottom()) tryBounce('bottom');
    }, { passive: true });
}

/**
 * @param {Document|Element} root
 */
function attachAll(root) {
    root.querySelectorAll(BOUNCE_SELECTORS).forEach(el => {
        if (el._bounceBound) return;
        el._bounceBound = true;
        attachBounce(el);
    });
}

/**
 * Inicializa el sistema de rebote en todos los contenedores scrollables
 * actuales y futuros (via MutationObserver).
 */
export function initOverscrollBounce() {
    attachAll(document);

    new MutationObserver((mutations) => {
        for (const m of mutations) {
            for (const node of m.addedNodes) {
                if (node.nodeType !== 1) continue;
                if (node.matches?.(BOUNCE_SELECTORS) && !node._bounceBound) {
                    node._bounceBound = true;
                    attachBounce(node);
                }
                attachAll(node);
            }
        }
    }).observe(document.body, { childList: true, subtree: true });
}
