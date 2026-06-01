import { describe, it, expect } from 'vitest';
import { calculateLineTotal, calculateCartTotal, shouldShowTapaModal } from '../cart.js';

describe('calculateLineTotal', () => {
    it('returns price × quantity with no mods', () => {
        const item = { price: 10, quantity: 2, mods: [] };
        expect(calculateLineTotal(item)).toBe(20);
    });

    it('adds extra mod charges to the unit price', () => {
        const item = {
            price: 5, quantity: 3,
            mods: [{ action: 'add', amountCharged: 1.5 }, { action: 'add', amountCharged: 0.5 }],
        };
        expect(calculateLineTotal(item)).toBeCloseTo(21);
    });

    it('ignores remove mods when calculating total', () => {
        const item = {
            price: 8, quantity: 1,
            mods: [{ action: 'remove', amountCharged: 0 }],
        };
        expect(calculateLineTotal(item)).toBe(8);
    });

    it('returns 0 for zero quantity', () => {
        const item = { price: 5, quantity: 0, mods: [] };
        expect(calculateLineTotal(item)).toBe(0);
    });
});

describe('calculateCartTotal', () => {
    it('sums all line totals', () => {
        const items = [
            { price: 10, quantity: 2, mods: [] },
            { price: 5,  quantity: 1, mods: [] },
        ];
        expect(calculateCartTotal(items)).toBe(25);
    });

    it('returns 0 for empty cart', () => {
        expect(calculateCartTotal([])).toBe(0);
    });

    it('handles variant price correctly', () => {
        const items = [
            { price: 12.5, quantity: 2, mods: [] },
        ];
        expect(calculateCartTotal(items)).toBeCloseTo(25);
    });
});

describe('shouldShowTapaModal', () => {
    const baseCfg = { enabled: true, kitchenOpen: true, maxVariants: 3 };
    const products = [{ id: 1 }];

    it('returns true when all conditions met', () => {
        expect(shouldShowTapaModal(baseCfg, 1, 0, products)).toBe(true);
    });

    it('returns false when tapas disabled', () => {
        expect(shouldShowTapaModal({ ...baseCfg, enabled: false }, 1, 0, products)).toBe(false);
    });

    it('returns false when kitchen is closed', () => {
        expect(shouldShowTapaModal({ ...baseCfg, kitchenOpen: false }, 1, 0, products)).toBe(false);
    });

    it('returns false when no bar items in cart', () => {
        expect(shouldShowTapaModal(baseCfg, 0, 0, products)).toBe(false);
    });

    it('returns false when max variants already used', () => {
        expect(shouldShowTapaModal(baseCfg, 1, 3, products)).toBe(false);
    });

    it('returns false when no tapa products available', () => {
        expect(shouldShowTapaModal(baseCfg, 1, 0, [])).toBe(false);
    });

    it('returns false when all available tapa variants are already chosen', () => {
        const twoProducts = [{ id: 1 }, { id: 2 }];
        expect(shouldShowTapaModal({ ...baseCfg, maxVariants: 5 }, 3, 2, twoProducts)).toBe(false);
    });

    it('returns true when maxVariants not reached and untried products exist', () => {
        const twoProducts = [{ id: 1 }, { id: 2 }];
        expect(shouldShowTapaModal({ ...baseCfg, maxVariants: 5 }, 3, 1, twoProducts)).toBe(true);
    });
});
