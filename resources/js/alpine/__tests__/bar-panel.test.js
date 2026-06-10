import { describe, it, expect, vi, beforeEach } from 'vitest';
import {
    mergeBarOrders,
    mergeBillOrders,
    countPendingBarItems,
    billRequestPolling,
    notificationPolling,
    waiterCallPolling,
    barPanel,
} from '../bar-panel.js';

const urls = {
    billRequests:                 '/api/bill-requests',
    billDismissTemplate:          '/api/bill/__ID__/dismiss',
    payments:                     '/payments',
    notificationsReady:           '/api/notifications/ready',
    notificationsDismissTemplate: '/api/notifications/__ID__/dismiss',
    waiterCalls:                  '/api/waiter-calls',
    waiterDismissTemplate:        '/api/waiter/__ID__/dismiss',
    barPending:                   '/api/bar/pending',
    barItems:                     '/api/bar/items',
};

describe('mergeBarOrders', () => {
    it('preserves marking flag on in-flight items', () => {
        const current  = [{ id: 1, items: [{ id: 10, marking: true }] }];
        const incoming = [{ id: 1, items: [{ id: 10 }] }];
        const result   = mergeBarOrders(incoming, current);
        expect(result[0].items[0].marking).toBe(true);
    });

    it('resets all_ready to false on merged orders', () => {
        const result = mergeBarOrders([{ id: 1, items: [] }], []);
        expect(result[0].all_ready).toBe(false);
    });

    it('returns empty array for empty incoming', () => {
        expect(mergeBarOrders([], [{ id: 1, items: [] }])).toHaveLength(0);
    });
});

describe('mergeBillOrders', () => {
    it('preserves paying flag on in-flight orders', () => {
        const current  = [{ id: 1, paying: true }];
        const incoming = [{ id: 1 }];
        const result   = mergeBillOrders(incoming, current);
        expect(result[0].paying).toBe(true);
    });

    it('sets paying false for orders not currently paying', () => {
        const result = mergeBillOrders([{ id: 2 }], []);
        expect(result[0].paying).toBe(false);
    });
});

describe('countPendingBarItems', () => {
    it('counts items without marking flag', () => {
        const items = [{ marking: false }, { marking: true }, { marking: false }];
        expect(countPendingBarItems(items)).toBe(2);
    });

    it('returns 0 for empty list', () => {
        expect(countPendingBarItems([])).toBe(0);
    });

    it('returns 0 when all items are marking', () => {
        expect(countPendingBarItems([{ marking: true }, { marking: true }])).toBe(0);
    });
});

describe('billRequestPolling component', () => {
    beforeEach(() => vi.restoreAllMocks());

    it('starts with empty billOrders', () => {
        const comp = billRequestPolling(urls);
        expect(comp.billOrders).toEqual([]);
    });

    it('loads orders on successful poll', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            ok: true,
            json: async () => ({ orders: [{ id: 1 }] }),
        });
        const comp = billRequestPolling(urls);
        await comp.poll();
        expect(comp.billOrders[0].id).toBe(1);
    });

    it('removes dismissed order from list', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true, json: async () => ({}) });
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = billRequestPolling(urls);
        comp.billOrders = [{ id: 5, paying: false }];
        await comp.dismiss(5);
        expect(comp.billOrders).toHaveLength(0);
    });

    it('preserves order when dismiss fetch fails', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = billRequestPolling(urls);
        comp.billOrders = [{ id: 5 }];
        await comp.dismiss(5);
        expect(comp.billOrders).toHaveLength(1);
    });
});

describe('notificationPolling component', () => {
    beforeEach(() => vi.restoreAllMocks());

    it('initialises with provided ready orders', () => {
        const comp = notificationPolling([{ id: 3 }], urls);
        expect(comp.readyOrders[0].id).toBe(3);
    });

    it('updates readyOrders on successful poll', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            ok: true,
            json: async () => ({ orders: [{ id: 7 }] }),
        });
        const comp = notificationPolling([], urls);
        await comp.poll();
        expect(comp.readyOrders[0].id).toBe(7);
    });

    it('removes dismissed order from readyOrders', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true, json: async () => ({}) });
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = notificationPolling([{ id: 9 }], urls);
        await comp.dismiss(9);
        expect(comp.readyOrders).toHaveLength(0);
    });
});

describe('waiterCallPolling component', () => {
    beforeEach(() => vi.restoreAllMocks());

    it('starts with empty waiterOrders', () => {
        const comp = waiterCallPolling(urls);
        expect(comp.waiterOrders).toEqual([]);
    });

    it('loads orders on successful poll', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            ok: true,
            json: async () => ({ orders: [{ id: 11, table: 'Mesa 3' }] }),
        });
        const comp = waiterCallPolling(urls);
        await comp.poll();
        expect(comp.waiterOrders[0].id).toBe(11);
        expect(comp.waiterOrders[0].table).toBe('Mesa 3');
    });

    it('does nothing when poll returns non-ok', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: false });
        const comp = waiterCallPolling(urls);
        await comp.poll();
        expect(comp.waiterOrders).toHaveLength(0);
    });

    it('removes dismissed order from list', async () => {
        global.fetch = vi.fn().mockResolvedValue({ ok: true, json: async () => ({}) });
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = waiterCallPolling(urls);
        comp.waiterOrders = [{ id: 11, table: 'Mesa 3' }];
        await comp.dismiss(11);
        expect(comp.waiterOrders).toHaveLength(0);
    });

    it('preserves order when dismiss fetch fails', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = waiterCallPolling(urls);
        comp.waiterOrders = [{ id: 11 }];
        await comp.dismiss(11);
        expect(comp.waiterOrders).toHaveLength(1);
    });

    it('preserves called_at field from poll response', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            ok: true,
            json: async () => ({ orders: [{ id: 11, table: 'Mesa 3', called_at: '14:35' }] }),
        });
        const comp = waiterCallPolling(urls);
        await comp.poll();
        expect(comp.waiterOrders[0].called_at).toBe('14:35');
    });

    it('calls the correct dismiss URL replacing __ID__', async () => {
        let calledUrl = '';
        global.fetch = vi.fn().mockImplementation((url) => {
            calledUrl = url;
            return Promise.resolve({ ok: true, json: async () => ({}) });
        });
        document.querySelector = vi.fn(() => ({ content: 'token' }));

        const comp = waiterCallPolling(urls);
        comp.waiterOrders = [{ id: 42 }];
        await comp.dismiss(42);
        expect(calledUrl).toBe('/api/waiter/42/dismiss');
    });
});

describe('barPanel component', () => {
    beforeEach(() => vi.restoreAllMocks());

    it('starts polling=true and lastUpdated placeholder', () => {
        const comp = barPanel([], urls);
        expect(comp.polling).toBe(true);
        expect(comp.lastUpdated).toBe('--:--');
    });

    it('sets polling false when tick fetch fails', async () => {
        global.fetch = vi.fn().mockRejectedValue(new Error('net'));
        const comp = barPanel([], urls);
        await comp.tick();
        expect(comp.polling).toBe(false);
    });

    it('merges incoming orders on successful tick', async () => {
        global.fetch = vi.fn().mockResolvedValue({
            json: async () => ({ orders: [{ id: 2, items: [] }] }),
        });
        const comp = barPanel([], urls);
        await comp.tick();
        expect(comp.orders[0].id).toBe(2);
        expect(comp.polling).toBe(true);
    });
});
