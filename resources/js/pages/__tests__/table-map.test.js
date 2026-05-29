import { describe, it, expect } from 'vitest';
import {
    rectCorners,
    projectOnAxis,
    obbOverlaps,
    circleObbOverlaps,
    overlaps,
    segmentsIntersect,
    pointInPolygon,
    polygonWorldCorners,
    polygonOverlaps,
    canvasBoundsFor,
} from '../table-map.js';

// ─── helpers ──────────────────────────────────────────────────────────────────

function rect(x, y, w, h, rotation = 0) {
    return { position_x: x, position_y: y, width: w, height: h, rotation };
}

function circle(x, y, d) {
    return { shape: 'round', position_x: x, position_y: y, width: d, height: d, rotation: 0 };
}

// ─── rectCorners ──────────────────────────────────────────────────────────────

describe('rectCorners', () => {
    it('returns 4 corners for an axis-aligned rectangle', () => {
        const corners = rectCorners(rect(0, 0, 100, 60));
        expect(corners).toHaveLength(4);
    });

    it('corners span correct x range for 0-rotation rect', () => {
        const corners = rectCorners(rect(0, 0, 100, 60));
        const xs = corners.map(c => c.x);
        expect(Math.min(...xs)).toBeCloseTo(0);
        expect(Math.max(...xs)).toBeCloseTo(100);
    });

    it('corners span correct y range for 0-rotation rect', () => {
        const corners = rectCorners(rect(0, 0, 100, 60));
        const ys = corners.map(c => c.y);
        expect(Math.min(...ys)).toBeCloseTo(0);
        expect(Math.max(...ys)).toBeCloseTo(60);
    });

    it('rotation=90 swaps effective width/height span', () => {
        const corners = rectCorners(rect(0, 0, 100, 60, 90));
        const xs = corners.map(c => c.x);
        const ys = corners.map(c => c.y);
        const spanX = Math.max(...xs) - Math.min(...xs);
        const spanY = Math.max(...ys) - Math.min(...ys);
        expect(spanX).toBeCloseTo(60, 5);
        expect(spanY).toBeCloseTo(100, 5);
    });

    it('defaults rotation to 0 when omitted', () => {
        const item = { position_x: 0, position_y: 0, width: 50, height: 50 };
        expect(() => rectCorners(item)).not.toThrow();
        expect(rectCorners(item)).toHaveLength(4);
    });
});

// ─── projectOnAxis ────────────────────────────────────────────────────────────

describe('projectOnAxis', () => {
    const corners = [
        { x: 0, y: 0 },
        { x: 4, y: 0 },
        { x: 4, y: 3 },
        { x: 0, y: 3 },
    ];

    it('projects onto X axis correctly', () => {
        const { min, max } = projectOnAxis(corners, { x: 1, y: 0 });
        expect(min).toBe(0);
        expect(max).toBe(4);
    });

    it('projects onto Y axis correctly', () => {
        const { min, max } = projectOnAxis(corners, { x: 0, y: 1 });
        expect(min).toBe(0);
        expect(max).toBe(3);
    });

    it('returns same min and max for a single point', () => {
        const { min, max } = projectOnAxis([{ x: 3, y: 4 }], { x: 1, y: 0 });
        expect(min).toBe(max);
    });
});

// ─── obbOverlaps ──────────────────────────────────────────────────────────────

describe('obbOverlaps', () => {
    it('returns true for two overlapping axis-aligned rects', () => {
        expect(obbOverlaps(rect(0, 0, 100, 100), rect(50, 50, 100, 100))).toBe(true);
    });

    it('returns false for two non-overlapping axis-aligned rects', () => {
        expect(obbOverlaps(rect(0, 0, 100, 100), rect(200, 0, 100, 100))).toBe(false);
    });

    it('returns false when rects only touch on edge (strict <)', () => {
        // pa.max === pb.min → gap test uses <=, so touching = false overlap
        expect(obbOverlaps(rect(0, 0, 100, 100), rect(100, 0, 100, 100))).toBe(false);
    });

    it('returns true for partially-rotated overlapping rects', () => {
        const a = rect(0, 0, 80, 80, 0);
        const b = rect(40, 40, 80, 80, 45);
        expect(obbOverlaps(a, b)).toBe(true);
    });

    it('returns false for clearly separated rotated rects', () => {
        const a = rect(0, 0, 50, 50, 0);
        const b = rect(300, 300, 50, 50, 45);
        expect(obbOverlaps(a, b)).toBe(false);
    });
});

// ─── circleObbOverlaps ────────────────────────────────────────────────────────

describe('circleObbOverlaps', () => {
    it('returns true when circle center is inside rect', () => {
        const c = { position_x: 40, position_y: 40, width: 20, height: 20 };
        expect(circleObbOverlaps(c, rect(0, 0, 100, 100))).toBe(true);
    });

    it('returns false when circle is far from rect', () => {
        const c = { position_x: 300, position_y: 300, width: 20, height: 20 };
        expect(circleObbOverlaps(c, rect(0, 0, 100, 100))).toBe(false);
    });

    it('returns true when circle overlaps rect corner', () => {
        // circle radius 30, center at (0, 0), rect at (20, 20)
        const c = { position_x: -30, position_y: -30, width: 60, height: 60 }; // center (0,0), r=30
        expect(circleObbOverlaps(c, rect(10, 10, 60, 60))).toBe(true);
    });

    it('returns false when circle is just outside rect edge', () => {
        // circle radius 10, center at 125; rect from 0 to 100
        const c = { position_x: 115, position_y: 45, width: 20, height: 20 }; // center (125, 55), r=10
        expect(circleObbOverlaps(c, rect(0, 0, 100, 100))).toBe(false);
    });
});

// ─── overlaps (dispatch) ─────────────────────────────────────────────────────

describe('overlaps', () => {
    it('circle vs circle: true when they intersect', () => {
        expect(overlaps(circle(0, 0, 60), circle(40, 0, 60))).toBe(true);
    });

    it('circle vs circle: false when apart', () => {
        expect(overlaps(circle(0, 0, 20), circle(200, 0, 20))).toBe(false);
    });

    it('rect vs rect: true when overlapping', () => {
        const a = { shape: 'square', ...rect(0, 0, 100, 100) };
        const b = { shape: 'square', ...rect(50, 50, 100, 100) };
        expect(overlaps(a, b)).toBe(true);
    });

    it('circle vs rect: true when circle overlaps rect', () => {
        const c = { shape: 'round', position_x: 60, position_y: 40, width: 40, height: 40 };
        const r = { shape: 'square', ...rect(0, 0, 100, 100) };
        expect(overlaps(c, r)).toBe(true);
    });

    it('rect vs circle: delegates correctly (symmetric)', () => {
        const c = { shape: 'round', position_x: 60, position_y: 40, width: 40, height: 40 };
        const r = { shape: 'square', ...rect(0, 0, 100, 100) };
        expect(overlaps(r, c)).toBe(true);
    });

    it('stool shape treated as round', () => {
        const a = { shape: 'stool', position_x: 0, position_y: 0, width: 20, height: 20 };
        const b = { shape: 'stool', position_x: 10, position_y: 0, width: 20, height: 20 };
        expect(overlaps(a, b)).toBe(true);
    });
});

// ─── segmentsIntersect ────────────────────────────────────────────────────────

describe('segmentsIntersect', () => {
    it('returns true for crossing segments', () => {
        expect(segmentsIntersect(
            { x: 0, y: 0 }, { x: 10, y: 10 },
            { x: 0, y: 10 }, { x: 10, y: 0 },
        )).toBe(true);
    });

    it('returns false for parallel non-overlapping segments', () => {
        expect(segmentsIntersect(
            { x: 0, y: 0 }, { x: 10, y: 0 },
            { x: 0, y: 5 }, { x: 10, y: 5 },
        )).toBe(false);
    });

    it('returns false for collinear segments (cross ≈ 0)', () => {
        expect(segmentsIntersect(
            { x: 0, y: 0 }, { x: 10, y: 0 },
            { x: 2, y: 0 }, { x: 8, y: 0 },
        )).toBe(false);
    });

    it('returns false when segments are aligned but do not reach each other', () => {
        expect(segmentsIntersect(
            { x: 0, y: 0 }, { x: 4, y: 0 },
            { x: 6, y: -2 }, { x: 6, y: 2 },
        )).toBe(false);
    });

    it('returns true when T-intersection at endpoint', () => {
        expect(segmentsIntersect(
            { x: 0, y: 0 }, { x: 10, y: 0 },
            { x: 5, y: -5 }, { x: 5, y: 5 },
        )).toBe(true);
    });
});

// ─── pointInPolygon ───────────────────────────────────────────────────────────

describe('pointInPolygon', () => {
    const square = [
        { x: 0, y: 0 }, { x: 10, y: 0 },
        { x: 10, y: 10 }, { x: 0, y: 10 },
    ];

    it('returns true for point inside polygon', () => {
        expect(pointInPolygon({ x: 5, y: 5 }, square)).toBe(true);
    });

    it('returns false for point outside polygon', () => {
        expect(pointInPolygon({ x: 20, y: 20 }, square)).toBe(false);
    });

    it('returns false for point on left of polygon', () => {
        expect(pointInPolygon({ x: -1, y: 5 }, square)).toBe(false);
    });

    it('works with a triangle', () => {
        const tri = [{ x: 0, y: 0 }, { x: 10, y: 0 }, { x: 5, y: 10 }];
        expect(pointInPolygon({ x: 5, y: 5 }, tri)).toBe(true);
        expect(pointInPolygon({ x: 5, y: 11 }, tri)).toBe(false);
    });
});

// ─── polygonWorldCorners ──────────────────────────────────────────────────────

describe('polygonWorldCorners', () => {
    it('falls back to rectCorners when vertices is empty', () => {
        const zone = { position_x: 0, position_y: 0, width: 100, height: 60, vertices: [] };
        const result = polygonWorldCorners(zone);
        expect(result).toHaveLength(4);
    });

    it('falls back to rectCorners when vertices < 3', () => {
        const zone = {
            position_x: 0, position_y: 0, width: 100, height: 60,
            vertices: [{ x: 0, y: 0 }, { x: 50, y: 0 }],
        };
        expect(polygonWorldCorners(zone)).toHaveLength(4);
    });

    it('returns same count as vertices when >= 3', () => {
        const zone = {
            position_x: 0, position_y: 0, width: 100, height: 60, rotation: 0,
            vertices: [{ x: 0, y: 0 }, { x: 50, y: 0 }, { x: 25, y: 30 }],
        };
        expect(polygonWorldCorners(zone)).toHaveLength(3);
    });

    it('falls back when vertices is undefined', () => {
        const zone = { position_x: 0, position_y: 0, width: 100, height: 60 };
        expect(polygonWorldCorners(zone)).toHaveLength(4);
    });
});

// ─── polygonOverlaps ──────────────────────────────────────────────────────────

describe('polygonOverlaps', () => {
    const squareA = [
        { x: 0, y: 0 }, { x: 10, y: 0 },
        { x: 10, y: 10 }, { x: 0, y: 10 },
    ];
    const squareB = [
        { x: 5, y: 5 }, { x: 15, y: 5 },
        { x: 15, y: 15 }, { x: 5, y: 15 },
    ];
    const squareC = [
        { x: 20, y: 20 }, { x: 30, y: 20 },
        { x: 30, y: 30 }, { x: 20, y: 30 },
    ];

    it('returns true for overlapping squares', () => {
        expect(polygonOverlaps(squareA, squareB)).toBe(true);
    });

    it('returns false for non-overlapping squares', () => {
        expect(polygonOverlaps(squareA, squareC)).toBe(false);
    });

    it('returns true when one polygon is inside the other', () => {
        const inner = [
            { x: 2, y: 2 }, { x: 8, y: 2 },
            { x: 8, y: 8 }, { x: 2, y: 8 },
        ];
        expect(polygonOverlaps(squareA, inner)).toBe(true);
    });
});

// ─── canvasBoundsFor ─────────────────────────────────────────────────────────

describe('canvasBoundsFor', () => {
    it('returns 0 for minX and minY for axis-aligned item at 0 rotation', () => {
        const bounds = canvasBoundsFor({ width: 100, height: 60, rotation: 0 }, 800, 600);
        expect(bounds.minX).toBeCloseTo(0);
        expect(bounds.minY).toBeCloseTo(0);
    });

    it('maxX = canvasW - width for 0-rotation item', () => {
        const bounds = canvasBoundsFor({ width: 100, height: 60, rotation: 0 }, 800, 600);
        expect(bounds.maxX).toBeCloseTo(700);
    });

    it('maxY = canvasH - height for 0-rotation item', () => {
        const bounds = canvasBoundsFor({ width: 100, height: 60, rotation: 0 }, 800, 600);
        expect(bounds.maxY).toBeCloseTo(540);
    });

    it('rotated 90° swaps effective dimensions', () => {
        const b0  = canvasBoundsFor({ width: 100, height: 60, rotation: 0  }, 800, 600);
        const b90 = canvasBoundsFor({ width: 100, height: 60, rotation: 90 }, 800, 600);
        // At 90° the effective half-extents swap; maxX and maxY should differ from 0-rotation
        expect(b90.maxX).not.toBeCloseTo(b0.maxX);
        expect(b90.maxY).not.toBeCloseTo(b0.maxY);
    });

    it('defaults rotation to 0 when omitted', () => {
        const item   = { width: 100, height: 60 };
        const bounds = canvasBoundsFor(item, 800, 600);
        expect(bounds.minX).toBeCloseTo(0);
        expect(bounds.maxX).toBeCloseTo(700);
    });

    it('returns correct bounds for square item', () => {
        const bounds = canvasBoundsFor({ width: 80, height: 80, rotation: 0 }, 500, 400);
        expect(bounds.maxX).toBeCloseTo(420);
        expect(bounds.maxY).toBeCloseTo(320);
    });
});
