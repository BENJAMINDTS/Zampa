import { describe, it, expect } from 'vitest';
import { getPinColor, filterBusinessesWithCoords, buildPopupContent } from '../superadmin-map.js';

describe('getPinColor', () => {
    it('returns plan colour for active businesses', () => {
        expect(getPinColor('#3b82f6', true)).toBe('#3b82f6');
    });

    it('returns red for inactive businesses regardless of plan colour', () => {
        expect(getPinColor('#3b82f6', false)).toBe('#ef4444');
    });

    it('returns red for inactive business with no-plan colour', () => {
        expect(getPinColor('#9ca3af', false)).toBe('#ef4444');
    });
});

describe('filterBusinessesWithCoords', () => {
    it('keeps businesses that have lat and lng', () => {
        const list = [
            { id: 1, lat: 40.4, lng: -3.7 },
            { id: 2, lat: null, lng: -3.7 },
            { id: 3, lat: 41.0, lng: null },
            { id: 4, lat: 39.5, lng: -3.2 },
        ];
        const result = filterBusinessesWithCoords(list);
        expect(result).toHaveLength(2);
        expect(result.map(b => b.id)).toEqual([1, 4]);
    });

    it('returns empty array when no businesses have coordinates', () => {
        expect(filterBusinessesWithCoords([{ id: 1, lat: null, lng: null }])).toHaveLength(0);
    });

    it('returns empty array for empty input', () => {
        expect(filterBusinessesWithCoords([])).toHaveLength(0);
    });
});

describe('buildPopupContent', () => {
    it('includes name, plan, and status', () => {
        const html = buildPopupContent({ name: 'Bar Sol', plan: 'Pro', active: true });
        expect(html).toContain('Bar Sol');
        expect(html).toContain('Pro');
        expect(html).toContain('Activo');
    });

    it('shows Inactivo for inactive businesses', () => {
        const html = buildPopupContent({ name: 'Closed', plan: 'Free', active: false });
        expect(html).toContain('Inactivo');
    });

    it('includes address when provided', () => {
        const html = buildPopupContent({ name: 'A', plan: 'B', active: true, address: 'Calle Mayor 1' });
        expect(html).toContain('Calle Mayor 1');
    });

    it('omits address line when address is falsy', () => {
        const html = buildPopupContent({ name: 'A', plan: 'B', active: true, address: null });
        expect(html).not.toContain('<br>null');
        expect(html).not.toContain('undefined');
    });
});
