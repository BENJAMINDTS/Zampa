import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mergeKitchenOrders, isOrderComplete, kitchenPanel } from '../kitchen-panel.js';

describe('mergeKitchenOrders', () => {
    it('preserves marking flag on in-flight items', () => {
        const current = [{
            id: 1,
            items: [{ id: 10, marking: true }, { id: 11, marking: false }],
        }];
        const incoming = [{
            id: 1,
            items: [{ id: 10 }, { id: 11 }],
        }];

        const result = mergeKitchenOrders(incoming, current);
        expect(result[0].items[0].marking).toBe(true);
        expect(result[0].items[1].marking).toBe(false);
    });

    it('sets all_ready and serving to false on merged orders', () => {
        const result = mergeKitchenOrders([{ id: 1, items: [] }], []);
        expect(result[0].all_ready).toBe(false);
        expect(result[0].serving).toBe(false);
    });

    it('handles empty incoming array', () => {
        const result = mergeKitchenOrders([], [{ id: 1, items: [] }]);
        expect(result).toHaveLength(0);
    });

    it('handles empty current array without throwing', () => {
        const incoming = [{ id: 1, items: [{ id: 5 }] }];
        const result   = mergeKitchenOrders(incoming, []);
        expect(result[0].items[0].marking).toBe(false);
    });
});

describe('isOrderComplete', () => {
    it('returns true when items list is empty', () => {
        expect(isOrderComplete([])).toBe(true);
    });

    it('returns false when items remain', () => {
        expect(isOrderComplete([{ id: 1 }])).toBe(false);
    });
});

describe('kitchenPanel component', () => {
    let urls;

    beforeEach(() => {
        vi.restoreAllMocks();
        urls = { pending: '/cocina/pending' };
    });

    it('initialises with provided orders', () => {
        const orders = [{ id: 1, items: [] }];
        const comp   = kitchenPanel(orders, urls);
        expect(comp.orders).toEqual(orders);
    });

    it('starts with polling true and placeholder lastUpdated', () => {
        const comp = kitchenPanel([], urls);
        expect(comp.polling).toBe(true);
        expect(comp.lastUpdated).toBe('--:--');
    });

    it('sets polling false when tick fetch fails', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        const comp = kitchenPanel([], urls);
        await comp.tick();
        expect(comp.polling).toBe(false);
    });

    it('merges incoming orders on successful tick', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: async () => ({ orders: [{ id: 2, items: [{ id: 20 }] }] }),
        });
        const comp = kitchenPanel([], urls);
        await comp.tick();
        expect(comp.polling).toBe(true);
        expect(comp.orders[0].id).toBe(2);
        expect(comp.orders[0].items[0].marking).toBe(false);
    });

    it('removes item and sets all_ready when server confirms complete order', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: async () => ({ all_ready: true }),
        });
        document.querySelector = vi.fn(() => ({ content: 'csrf-token' }));

        const order = { id: 1, items: [{ id: 10, marking: false }], all_ready: false };
        const comp  = kitchenPanel([order], urls);
        await comp.markReady(order.items[0], order);

        expect(order.items).toHaveLength(0);
        expect(order.all_ready).toBe(true);
    });

    it('restores marking false when markReady fetch fails', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const item  = { id: 10, marking: false };
        const order = { id: 1, items: [item], all_ready: false };
        const comp  = kitchenPanel([order], urls);
        await comp.markReady(item, order);

        expect(item.marking).toBe(false);
    });
});
