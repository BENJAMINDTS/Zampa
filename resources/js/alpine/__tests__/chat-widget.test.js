import { describe, it, expect } from 'vitest';
import {
    formatTime,
    getCategoryEmoji,
    getFoodIcon,
    getAllergenIcon,
    getQrStyle,
} from '../chat-widget.js';

describe('formatTime', () => {
    it('returns empty string for falsy ts', () => {
        expect(formatTime(Date.now(), 0)).toBe('');
        expect(formatTime(Date.now(), null)).toBe('');
    });

    it('returns Ahora when diff < 60 s', () => {
        const now = 1000000;
        expect(formatTime(now, now - 30000)).toBe('Ahora');
    });

    it('returns HH:MM when diff >= 60 s', () => {
        const ts  = new Date('2024-01-01T14:05:00').getTime();
        const now = ts + 120000;
        expect(formatTime(now, ts)).toBe('14:05');
    });

    it('pads single-digit hours and minutes', () => {
        const ts  = new Date('2024-01-01T08:03:00').getTime();
        const now = ts + 120000;
        expect(formatTime(now, ts)).toBe('08:03');
    });
});

describe('getCategoryEmoji', () => {
    it('returns pizza emoji for "Pizza"', () => {
        expect(getCategoryEmoji('Pizza')).toBe('🍕');
    });

    it('returns burger emoji for "Hamburguesas"', () => {
        expect(getCategoryEmoji('Hamburguesas')).toBe('🍔');
    });

    it('returns beer emoji for "Cervezas"', () => {
        expect(getCategoryEmoji('Cervezas')).toBe('🍺');
    });

    it('returns wine emoji for "Vinos"', () => {
        expect(getCategoryEmoji('Vinos')).toBe('🍷');
    });

    it('returns dessert emoji for "Postres"', () => {
        expect(getCategoryEmoji('Postres')).toBe('🍰');
    });

    it('returns default emoji for unknown category', () => {
        expect(getCategoryEmoji('Desconocida')).toBe('🍽️');
    });

    it('handles empty string', () => {
        expect(getCategoryEmoji('')).toBe('🍽️');
    });

    it('handles accented names (Pastas)', () => {
        expect(getCategoryEmoji('Pastas')).toBe('🍝');
    });
});

describe('getFoodIcon', () => {
    it('returns pizza icon for pizza category', () => {
        const result = getFoodIcon('Pizza', 'Margarita');
        expect(result?.svgId).toBe('pizza');
        expect(result?.label).toBe('Pizza');
    });

    it('returns burger icon', () => {
        const result = getFoodIcon('Hamburguesas', 'Burger clásica');
        expect(result?.svgId).toBe('burger');
    });

    it('returns meat icon for carne', () => {
        const result = getFoodIcon('Carnes', 'Filete de ternera');
        expect(result?.svgId).toBe('meat');
    });

    it('returns drink icon for cafe', () => {
        const result = getFoodIcon('Bebidas', 'Café con leche');
        expect(result?.svgId).toBe('drink');
    });

    it('returns null for unrecognized combination', () => {
        expect(getFoodIcon('Misc', 'Plato especial')).toBeNull();
    });

    it('matches pasta in product name even with plain category', () => {
        const result = getFoodIcon('Primeros', 'Espaguetis carbonara');
        expect(result?.svgId).toBe('pasta');
    });
});

describe('getAllergenIcon', () => {
    it('returns gluten icon for "Trigo"', () => {
        const result = getAllergenIcon('Trigo');
        expect(result.svgId).toBe('gluten');
        expect(result.label).toBe('Gluten');
    });

    it('returns egg icon for "Huevo"', () => {
        expect(getAllergenIcon('Huevo').svgId).toBe('egg');
    });

    it('returns milk icon for "Lactosa"', () => {
        expect(getAllergenIcon('Lactosa').svgId).toBe('milk');
    });

    it('returns nuts icon for "Almendra"', () => {
        expect(getAllergenIcon('Almendra').svgId).toBe('nuts');
    });

    it('returns fish icon for "Salmón"', () => {
        expect(getAllergenIcon('Salmón').svgId).toBe('fish');
    });

    it('returns soy icon for "Soja"', () => {
        expect(getAllergenIcon('Soja').svgId).toBe('soy');
    });

    it('returns null svgId for unknown allergen and preserves original name as label', () => {
        const result = getAllergenIcon('Desconocido');
        expect(result.svgId).toBeNull();
        expect(result.label).toBe('Desconocido');
    });

    it('returns molluscs icon for "Pulpo"', () => {
        expect(getAllergenIcon('Pulpo').svgId).toBe('molluscs');
    });
});

describe('getQrStyle', () => {
    it('returns green style for "Confirmar pedido"', () => {
        const style = getQrStyle('Confirmar pedido');
        expect(style).toContain('#22C55E');
    });

    it('returns cyan style for "Ver mi pedido"', () => {
        const style = getQrStyle('Ver mi pedido');
        expect(style).toContain('#22D3EE');
    });

    it('returns default blue style for other labels', () => {
        const style = getQrStyle('Pizzas');
        expect(style).toContain('#8FA8E8');
    });

    it('includes border-radius in all styles', () => {
        expect(getQrStyle('Confirmar pedido')).toContain('border-radius:9999px');
        expect(getQrStyle('Otro')).toContain('border-radius:9999px');
    });
});
