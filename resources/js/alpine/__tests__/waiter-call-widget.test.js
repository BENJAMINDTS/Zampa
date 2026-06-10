import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { waiterCallWidget } from '../bill.js';

const HASH = 'test-hash-abc';

beforeEach(() => {
    vi.useFakeTimers();
    document.querySelector = vi.fn(() => ({ content: 'csrf-token' }));
});

afterEach(() => {
    vi.useRealTimers();
    vi.restoreAllMocks();
});

describe('waiterCallWidget', () => {
    it('starts with modal closed and not sent', () => {
        const w = waiterCallWidget(HASH);
        expect(w.modalOpen).toBe(false);
        expect(w.sent).toBe(false);
        expect(w.sending).toBe(false);
    });

    it('opens modal and resets countdown to 20', () => {
        const w = waiterCallWidget(HASH);
        w.countdown = 5;
        w.openModal();
        expect(w.modalOpen).toBe(true);
        expect(w.countdown).toBe(20);
    });

    it('closes modal on closeModal()', () => {
        const w = waiterCallWidget(HASH);
        w.openModal();
        w.closeModal();
        expect(w.modalOpen).toBe(false);
    });

    it('decrements countdown every second', () => {
        const w = waiterCallWidget(HASH);
        w.openModal();
        vi.advanceTimersByTime(5000);
        expect(w.countdown).toBe(15);
    });

    it('auto-closes modal when countdown reaches 0', () => {
        const w = waiterCallWidget(HASH);
        w.openModal();
        vi.advanceTimersByTime(20000);
        expect(w.modalOpen).toBe(false);
    });

    it('sends POST to correct URL on confirm', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(global.fetch).toHaveBeenCalledWith(
            `/api/v1/waiter-call/${HASH}`,
            expect.objectContaining({ method: 'POST' })
        );
    });

    it('sets sent=true after confirm', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(w.sent).toBe(true);
    });

    it('closes modal after confirm', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        w.openModal();
        await w.confirm();
        expect(w.modalOpen).toBe(false);
    });

    it('sets sent=true even when fetch throws', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(w.sent).toBe(true);
    });

    it('sets onCooldown true after confirm', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(w.onCooldown).toBe(true);
    });

    it('openModal does nothing while onCooldown', () => {
        const w = waiterCallWidget(HASH);
        w.onCooldown = true;
        w.openModal();
        expect(w.modalOpen).toBe(false);
    });

    it('clears onCooldown after 30 seconds', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(w.onCooldown).toBe(true);
        vi.advanceTimersByTime(30000);
        expect(w.onCooldown).toBe(false);
    });

    it('resets sent to false after 5 seconds', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true });
        const w = waiterCallWidget(HASH);
        await w.confirm();
        expect(w.sent).toBe(true);
        vi.advanceTimersByTime(5000);
        expect(w.sent).toBe(false);
    });
});
