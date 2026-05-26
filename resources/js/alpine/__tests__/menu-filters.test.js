import { describe, it, expect } from 'vitest';
import { isProductVisible, isCategoryVisible, toggleAllergen } from '../menu-filters.js';

const makeProduct = (overrides = {}) => ({
    id: 1,
    categoryId: 10,
    destination: 'kitchen',
    allergenTypes: [],
    ...overrides,
});

describe('isProductVisible', () => {
    it('returns true when no filters are active', () => {
        expect(isProductVisible(makeProduct(), null, null, [])).toBe(true);
    });

    it('hides product with wrong category', () => {
        const p = makeProduct({ categoryId: 10 });
        expect(isProductVisible(p, 99, null, [])).toBe(false);
    });

    it('shows product with matching category', () => {
        const p = makeProduct({ categoryId: 10 });
        expect(isProductVisible(p, 10, null, [])).toBe(true);
    });

    it('hides product with wrong destination', () => {
        const p = makeProduct({ destination: 'bar' });
        expect(isProductVisible(p, null, 'kitchen', [])).toBe(false);
    });

    it('shows product with matching destination', () => {
        const p = makeProduct({ destination: 'kitchen' });
        expect(isProductVisible(p, null, 'kitchen', [])).toBe(true);
    });

    it('hides product that contains an active allergen', () => {
        const p = makeProduct({ allergenTypes: ['gluten', 'milk'] });
        expect(isProductVisible(p, null, null, ['gluten'])).toBe(false);
    });

    it('shows product when none of its allergens are active', () => {
        const p = makeProduct({ allergenTypes: ['milk'] });
        expect(isProductVisible(p, null, null, ['gluten'])).toBe(true);
    });

    it('shows product again when allergen is deactivated', () => {
        const p = makeProduct({ allergenTypes: ['gluten'] });
        expect(isProductVisible(p, null, null, [])).toBe(true);
    });

    it('handles product with empty allergen list', () => {
        expect(isProductVisible(makeProduct(), null, null, ['gluten'])).toBe(true);
    });
});

describe('isCategoryVisible', () => {
    const products = [
        makeProduct({ id: 1, categoryId: 10, allergenTypes: [] }),
        makeProduct({ id: 2, categoryId: 10, allergenTypes: ['gluten'] }),
        makeProduct({ id: 3, categoryId: 20, allergenTypes: [] }),
    ];

    it('returns true when no filters active and category has products', () => {
        expect(isCategoryVisible(10, products, null, null, [])).toBe(true);
    });

    it('hides category when wrong activeCategory selected', () => {
        expect(isCategoryVisible(10, products, 20, null, [])).toBe(false);
    });

    it('returns true for empty category regardless of filters', () => {
        expect(isCategoryVisible(99, products, null, null, ['gluten'])).toBe(true);
    });

    it('hides category when all its products are filtered out by allergen', () => {
        const glutenProducts = [makeProduct({ id: 1, categoryId: 30, allergenTypes: ['gluten'] })];
        expect(isCategoryVisible(30, glutenProducts, null, null, ['gluten'])).toBe(false);
    });
});

describe('toggleAllergen', () => {
    it('adds allergen when not present', () => {
        const list = [];
        toggleAllergen(list, 'gluten');
        expect(list).toContain('gluten');
    });

    it('removes allergen when already present', () => {
        const list = ['gluten'];
        toggleAllergen(list, 'gluten');
        expect(list).toHaveLength(0);
    });

    it('does not affect other allergens in the list', () => {
        const list = ['milk', 'gluten'];
        toggleAllergen(list, 'milk');
        expect(list).toEqual(['gluten']);
    });
});
