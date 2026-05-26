import { describe, it, expect } from 'vitest';
import { addScheduleSlot, removeScheduleSlot, maxSlotsReached, emptySlot } from '../business-config.js';

describe('emptySlot', () => {
    it('returns object with blank opens_at and closes_at', () => {
        const slot = emptySlot();
        expect(slot.opens_at).toBe('');
        expect(slot.closes_at).toBe('');
    });
});

describe('addScheduleSlot', () => {
    it('appends a slot when below max', () => {
        const slots = [];
        addScheduleSlot(slots, 4);
        expect(slots).toHaveLength(1);
        expect(slots[0]).toEqual({ opens_at: '', closes_at: '' });
    });

    it('does not add when max is reached', () => {
        const slots = [{}, {}, {}, {}];
        addScheduleSlot(slots, 4);
        expect(slots).toHaveLength(4);
    });

    it('uses default max of 4', () => {
        const slots = [{}, {}, {}, {}];
        addScheduleSlot(slots);
        expect(slots).toHaveLength(4);
    });
});

describe('removeScheduleSlot', () => {
    it('removes slot at given index', () => {
        const slots = [{ opens_at: '08:00' }, { opens_at: '14:00' }];
        removeScheduleSlot(slots, 0);
        expect(slots).toHaveLength(1);
        expect(slots[0].opens_at).toBe('14:00');
    });

    it('handles removing the last slot', () => {
        const slots = [{ opens_at: '09:00' }];
        removeScheduleSlot(slots, 0);
        expect(slots).toHaveLength(0);
    });
});

describe('maxSlotsReached', () => {
    it('returns true when slots equal max', () => {
        expect(maxSlotsReached([{}, {}, {}, {}], 4)).toBe(true);
    });

    it('returns false when slots are below max', () => {
        expect(maxSlotsReached([{}, {}], 4)).toBe(false);
    });

    it('returns true when slots exceed max', () => {
        expect(maxSlotsReached([{}, {}, {}, {}, {}], 4)).toBe(true);
    });

    it('returns false for empty slots list', () => {
        expect(maxSlotsReached([], 4)).toBe(false);
    });

    it('uses default max of 4', () => {
        expect(maxSlotsReached([{}, {}, {}, {}])).toBe(true);
        expect(maxSlotsReached([{}, {}])).toBe(false);
    });
});
