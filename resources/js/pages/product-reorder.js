import Sortable from 'sortablejs';

/**
 * Drag-and-drop reordering for the products admin table.
 * Reads config from data attributes on the tbody element.
 */
export function initProductReorder() {
    const tbody = document.querySelector('tbody[data-reorder-url]');
    if (!tbody) return;

    const reorderUrl  = tbody.dataset.reorderUrl;
    const offset      = parseInt(tbody.dataset.offset, 10);
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;
    const feedbackOk  = document.getElementById('reorder-feedback');
    const feedbackErr = document.getElementById('reorder-error');
    let feedbackTimer = null;

    function showFeedback(el) {
        [feedbackOk, feedbackErr].forEach(e => e && e.classList.add('hidden'));
        if (el) {
            el.classList.remove('hidden');
            clearTimeout(feedbackTimer);
            feedbackTimer = setTimeout(() => el.classList.add('hidden'), 3000);
        }
    }

    Sortable.create(tbody, {
        handle: '.drag-handle',
        animation: 180,
        easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
        ghostClass: 'sortable-ghost',
        dragClass: 'sortable-drag',
        onEnd() {
            const ids = Array.from(tbody.querySelectorAll('tr[data-product-id]'))
                            .map(tr => parseInt(tr.dataset.productId, 10));

            fetch(reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ ids, offset }),
            })
            .then(res => {
                if (!res.ok) throw new Error('server error');
                showFeedback(feedbackOk);
            })
            .catch(() => showFeedback(feedbackErr));
        },
    });
}
