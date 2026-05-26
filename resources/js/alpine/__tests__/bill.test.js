import { describe, it, expect } from 'vitest';
import {
    calculateEquitativeShare,
    calculateTipFromPercent,
    calculateSelectedTotal,
} from '../bill.js';

describe('calculateEquitativeShare', () => {
    it('divides total evenly among people', () => {
        expect(calculateEquitativeShare(30, 3)).toBeCloseTo(10);
    });

    it('rounds to 2 decimal places', () => {
        expect(calculateEquitativeShare(10, 3)).toBe(3.33);
    });

    it('uses minimum of 2 when people is 1', () => {
        expect(calculateEquitativeShare(20, 1)).toBe(10);
    });

    it('uses minimum of 2 when people is 0', () => {
        expect(calculateEquitativeShare(20, 0)).toBe(10);
    });

    it('uses minimum of 2 when people is NaN string', () => {
        expect(calculateEquitativeShare(20, 'abc')).toBe(10);
    });

    it('handles 2 people correctly', () => {
        expect(calculateEquitativeShare(25, 2)).toBe(12.5);
    });

    it('handles large numbers of people', () => {
        const share = calculateEquitativeShare(100, 7);
        expect(share).toBe(14.29);
    });
});

describe('calculateTipFromPercent', () => {
    it('calculates 10% tip', () => {
        expect(calculateTipFromPercent(20, 10)).toBe(2);
    });

    it('calculates 5% tip', () => {
        expect(calculateTipFromPercent(20, 5)).toBe(1);
    });

    it('returns 0 for 0% tip', () => {
        expect(calculateTipFromPercent(50, 0)).toBe(0);
    });

    it('rounds to 2 decimal places', () => {
        expect(calculateTipFromPercent(33.33, 10)).toBe(3.33);
    });

    it('handles base of 0', () => {
        expect(calculateTipFromPercent(0, 15)).toBe(0);
    });
});

describe('calculateSelectedTotal', () => {
    const items = [
        { id: 1, total: 5.0 },
        { id: 2, total: 8.5 },
        { id: 3, total: 3.0 },
    ];

    it('sums selected item totals', () => {
        expect(calculateSelectedTotal(items, [1, 3])).toBeCloseTo(8.0);
    });

    it('returns 0 when no items selected', () => {
        expect(calculateSelectedTotal(items, [])).toBe(0);
    });

    it('returns full total when all items selected', () => {
        expect(calculateSelectedTotal(items, [1, 2, 3])).toBeCloseTo(16.5);
    });

    it('ignores IDs not in the list', () => {
        expect(calculateSelectedTotal(items, [99])).toBe(0);
    });

    it('handles empty items array', () => {
        expect(calculateSelectedTotal([], [1, 2])).toBe(0);
    });
});
