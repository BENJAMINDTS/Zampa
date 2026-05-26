import { describe, it, expect, vi, beforeEach } from 'vitest';
import { badgePolling } from '../layout-badges.js';

describe('badgePolling', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    it('initialises count to 0', () => {
        const comp = badgePolling('/kitchen/pending');
        expect(comp.count).toBe(0);
    });

    it('sets count from successful fetch response', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: async () => ({ pending_count: 7 }),
        });

        const comp = badgePolling('/kitchen/pending');
        await comp.fetchCount();

        expect(comp.count).toBe(7);
    });

    it('falls back to 0 when pending_count is missing', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: async () => ({}),
        });

        const comp = badgePolling('/bar/pending');
        await comp.fetchCount();

        expect(comp.count).toBe(0);
    });

    it('does not throw when fetch rejects', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('network error'));

        const comp = badgePolling('/kitchen/pending');
        await expect(comp.fetchCount()).resolves.toBeUndefined();
        expect(comp.count).toBe(0);
    });

    it('retains last known count when fetch fails', async () => {
        global.fetch = vi.fn()
            .mockResolvedValueOnce({ json: async () => ({ pending_count: 5 }) })
            .mockRejectedValueOnce(new Error('down'));

        const comp = badgePolling('/kitchen/pending');
        await comp.fetchCount();
        expect(comp.count).toBe(5);

        await comp.fetchCount();
        expect(comp.count).toBe(5);
    });
});
