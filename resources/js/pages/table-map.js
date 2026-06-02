/**
 * @fileoverview Table map Alpine component — interactive drag-and-drop floor plan editor.
 *   Includes SAT collision detection, floor management, undo/redo, and keyboard navigation.
 *   Registered as `Alpine.data('tableMap')` and several `Alpine.store` entries.
 *   Used in resources/views/tables/map.blade.php.
 * @module table-map
 * @author BenjaminDTS
 */

/**
 * Returns the 4 world-space corners of a rotated rectangle.
 *
 * @param {{ position_x: number, position_y: number, width: number, height: number, rotation?: number }} item
 * @returns {Array<{x: number, y: number}>}
 */
export function rectCorners(item) {
    const rad = (item.rotation ?? 0) * Math.PI / 180;
    const cos = Math.cos(rad), sin = Math.sin(rad);
    const cx  = item.position_x + item.width  / 2;
    const cy  = item.position_y + item.height / 2;
    const hw  = item.width  / 2, hh = item.height / 2;
    return [
        { x: cx + hw*cos - hh*sin, y: cy + hw*sin + hh*cos },
        { x: cx - hw*cos - hh*sin, y: cy - hw*sin + hh*cos },
        { x: cx - hw*cos + hh*sin, y: cy - hw*sin - hh*cos },
        { x: cx + hw*cos + hh*sin, y: cy + hw*sin - hh*cos },
    ];
}

/**
 * Projects a list of vertices onto a normalized axis and returns { min, max }.
 *
 * @param {Array<{x: number, y: number}>} corners
 * @param {{x: number, y: number}} axis
 * @returns {{ min: number, max: number }}
 */
export function projectOnAxis(corners, axis) {
    let min = Infinity, max = -Infinity;
    for (const c of corners) {
        const p = c.x * axis.x + c.y * axis.y;
        if (p < min) min = p;
        if (p > max) max = p;
    }
    return { min, max };
}

/**
 * SAT overlap test between two oriented bounding boxes (OBB vs OBB).
 *
 * @param {{ position_x: number, position_y: number, width: number, height: number, rotation?: number }} a
 * @param {{ position_x: number, position_y: number, width: number, height: number, rotation?: number }} b
 * @returns {boolean}
 */
export function obbOverlaps(a, b) {
    const ca = rectCorners(a);
    const cb = rectCorners(b);
    const ra = (a.rotation ?? 0) * Math.PI / 180;
    const rb = (b.rotation ?? 0) * Math.PI / 180;
    const axes = [
        { x:  Math.cos(ra), y: Math.sin(ra) },
        { x: -Math.sin(ra), y: Math.cos(ra) },
        { x:  Math.cos(rb), y: Math.sin(rb) },
        { x: -Math.sin(rb), y: Math.cos(rb) },
    ];
    for (const ax of axes) {
        const pa = projectOnAxis(ca, ax);
        const pb = projectOnAxis(cb, ax);
        if (pa.max <= pb.min || pb.max <= pa.min) return false;
    }
    return true;
}

/**
 * Circle vs OBB overlap test. Transforms the circle center to the rectangle's local space.
 *
 * @param {{ position_x: number, position_y: number, width: number }} circle
 * @param {{ position_x: number, position_y: number, width: number, height: number, rotation?: number }} rect
 * @returns {boolean}
 */
export function circleObbOverlaps(circle, rect) {
    const cx  = circle.position_x + circle.width  / 2;
    const cy  = circle.position_y + circle.height / 2;
    const cr  = circle.width / 2;
    const rad = (rect.rotation ?? 0) * Math.PI / 180;
    const cos = Math.cos(rad), sin = Math.sin(rad);
    const rx  = rect.position_x + rect.width  / 2;
    const ry  = rect.position_y + rect.height / 2;
    const lx  = (cx - rx) * cos + (cy - ry) * sin;
    const ly  = -(cx - rx) * sin + (cy - ry) * cos;
    const hw  = rect.width / 2, hh = rect.height / 2;
    const dx  = lx - Math.max(-hw, Math.min(hw, lx));
    const dy  = ly - Math.max(-hh, Math.min(hh, ly));
    return (dx * dx + dy * dy) < cr * cr;
}

/**
 * Returns true if two items overlap, handling round/rectangle shape combinations.
 *
 * @param {{ shape?: string, position_x: number, position_y: number, width: number, height: number, rotation?: number }} a
 * @param {{ shape?: string, position_x: number, position_y: number, width: number, height: number, rotation?: number }} b
 * @returns {boolean}
 */
export function overlaps(a, b) {
    const aRound = a.shape === 'round' || a.shape === 'stool';
    const bRound = b.shape === 'round' || b.shape === 'stool';
    if (aRound && bRound) {
        const dx = (a.position_x + a.width  / 2) - (b.position_x + b.width  / 2);
        const dy = (a.position_y + a.height / 2) - (b.position_y + b.height / 2);
        const r  = a.width / 2 + b.width / 2;
        return (dx * dx + dy * dy) < r * r;
    }
    if (aRound) return circleObbOverlaps(a, b);
    if (bRound) return circleObbOverlaps(b, a);
    return obbOverlaps(a, b);
}

/**
 * Returns true if line segment p1→p2 intersects p3→p4.
 *
 * @param {{x: number, y: number}} p1
 * @param {{x: number, y: number}} p2
 * @param {{x: number, y: number}} p3
 * @param {{x: number, y: number}} p4
 * @returns {boolean}
 */
export function segmentsIntersect(p1, p2, p3, p4) {
    const dx1 = p2.x - p1.x, dy1 = p2.y - p1.y;
    const dx2 = p4.x - p3.x, dy2 = p4.y - p3.y;
    const cross = dx1 * dy2 - dy1 * dx2;
    if (Math.abs(cross) < 1e-10) return false;
    const t = ((p3.x - p1.x) * dy2 - (p3.y - p1.y) * dx2) / cross;
    const u = ((p3.x - p1.x) * dy1 - (p3.y - p1.y) * dx1) / cross;
    return t >= 0 && t <= 1 && u >= 0 && u <= 1;
}

/**
 * Ray-casting point-in-polygon test (works with concave polygons).
 *
 * @param {{x: number, y: number}} pt
 * @param {Array<{x: number, y: number}>} poly
 * @returns {boolean}
 */
export function pointInPolygon(pt, poly) {
    let inside = false;
    const n = poly.length;
    for (let i = 0, j = n - 1; i < n; j = i++) {
        const xi = poly[i].x, yi = poly[i].y;
        const xj = poly[j].x, yj = poly[j].y;
        if (((yi > pt.y) !== (yj > pt.y)) &&
            (pt.x < (xj - xi) * (pt.y - yi) / (yj - yi) + xi)) {
            inside = !inside;
        }
    }
    return inside;
}

/**
 * Returns world-space corners of a zone polygon (or rect fallback if no vertices).
 *
 * @param {{ position_x: number, position_y: number, width: number, height: number, rotation?: number, vertices?: Array<{x:number,y:number}> }} zone
 * @returns {Array<{x: number, y: number}>}
 */
export function polygonWorldCorners(zone) {
    if (!zone.vertices || zone.vertices.length < 3) return rectCorners(zone);
    const rad = (zone.rotation ?? 0) * Math.PI / 180;
    const cos = Math.cos(rad), sin = Math.sin(rad);
    const cx  = zone.position_x + zone.width  / 2;
    const cy  = zone.position_y + zone.height / 2;
    return zone.vertices.map(v => {
        const wx = zone.position_x + v.x - cx;
        const wy = zone.position_y + v.y - cy;
        return { x: cx + wx * cos - wy * sin, y: cy + wx * sin + wy * cos };
    });
}

/**
 * Returns true if two convex polygons overlap (edge intersections + point containment).
 *
 * @param {Array<{x: number, y: number}>} polyA
 * @param {Array<{x: number, y: number}>} polyB
 * @returns {boolean}
 */
export function polygonOverlaps(polyA, polyB) {
    const na = polyA.length, nb = polyB.length;
    for (let i = 0; i < na; i++) {
        for (let j = 0; j < nb; j++) {
            if (segmentsIntersect(polyA[i], polyA[(i + 1) % na], polyB[j], polyB[(j + 1) % nb])) return true;
        }
    }
    if (pointInPolygon(polyA[0], polyB)) return true;
    if (pointInPolygon(polyB[0], polyA)) return true;
    return false;
}

/**
 * Returns the valid clamping bounds for an item on a canvas, accounting for rotation.
 *
 * @param {{ width: number, height: number, rotation?: number }} item
 * @param {number} canvasW
 * @param {number} canvasH
 * @returns {{ minX: number, maxX: number, minY: number, maxY: number }}
 */
export function canvasBoundsFor(item, canvasW, canvasH) {
    const rad   = (item.rotation ?? 0) * Math.PI / 180;
    const cos   = Math.abs(Math.cos(rad));
    const sin   = Math.abs(Math.sin(rad));
    const hw    = item.width  / 2;
    const hh    = item.height / 2;
    const halfW = hw * cos + hh * sin;
    const halfH = hw * sin + hh * cos;
    return {
        minX: halfW - hw,
        maxX: canvasW - hw - halfW,
        minY: halfH - hh,
        maxY: canvasH - hh - halfH,
    };
}

/**
 * Reads map initialization data from `<script id="map-init" type="application/json">`.
 *
 * @returns {Object} Parsed init object or empty object.
 */
export function readMapInit() {
    const raw = document.getElementById('map-init');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Reads map URL config from `<script id="map-urls" type="application/json">`.
 *
 * @returns {Object} Parsed URLs object or empty object.
 */
export function readMapUrls() {
    const raw = document.getElementById('map-urls');
    return raw ? JSON.parse(raw.textContent) : {};
}

/**
 * Registers all Alpine stores and `Alpine.data('tableMap')` for the table map.
 * Reads initial state from `<script id="map-init">` and URLs from `<script id="map-urls">`.
 *
 * @returns {void}
 */
export function registerTableMap() {
    Alpine.store('helpModal', { show: false });

    Alpine.store('tableModal', {
        open:     false,
        name:     '',
        mode:     'table',
        _resolve: null,

        prompt(mode = 'table') {
            this.name = '';
            this.mode = mode;
            this.open = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        confirm() {
            if (this.mode === 'zone' && !this.name.trim()) return;
            const name = this.name.trim();
            this.open = false;
            this._resolve?.(name);
        },

        cancel() {
            this.open = false;
            this._resolve?.(null);
        },
    });

    Alpine.store('qrModal', {
        show:   false,
        table:  null,
        qrSvg:  '',

        open(table) {
            this.table = table;
            this.show  = true;

            const cached = Alpine.store('viewPanel')._qrCache[table.id];
            if (cached) { this.qrSvg = cached; return; }

            this.qrSvg = '';
            fetch(`/mesas/${table.id}/qr?h=${table.unique_hash}`)
                .then(r => r.text())
                .then(svg => {
                    Alpine.store('viewPanel')._qrCache[table.id] = svg;
                    if (this.table?.id === table.id) this.qrSvg = svg;
                })
                .catch(() => {});
        },

        close() {
            this.show  = false;
            this.table = null;
            this.qrSvg = '';
        },
    });

    Alpine.store('viewPanel', {
        show:      false,
        table:     null,
        qrSvg:     '',
        _qrCache:  {},

        open(table) {
            // Spread into a plain object — the x-for proxy from tableMap loses
            // reactive tracking when stored outside its component scope.
            this.table = {
                id:               table.id,
                name:             table.name,
                unique_hash:      table.unique_hash,
                orderStatus:      table.orderStatus,
                shape:            table.shape,
                floor:            table.floor      ?? 1,
                zone_id:          table.zone_id    ?? null,
                position_x:       table.position_x ?? 0,
                position_y:       table.position_y ?? 0,
                is_service_point: table.is_service_point,
            };
            this.show = true;

            if (this._qrCache[table.id]) {
                this.qrSvg = this._qrCache[table.id];
                return;
            }

            // Fallback: mesa no estaba en caché aún (carga en progreso o fallo previo).
            this.qrSvg = '';
            fetch(`/mesas/${table.id}/qr?h=${table.unique_hash}`)
                .then(r => r.text())
                .then(svg => {
                    this._qrCache[table.id] = svg;
                    if (this.table?.id === table.id) this.qrSvg = svg;
                })
                .catch(() => {});
        },

        close() {
            this.show  = false;
            this.table = null;
            this.qrSvg = '';
        },
    });

    Alpine.store('deleteModal', {
        show:     false,
        table:    null,
        _resolve: null,

        prompt(table) {
            this.table = table;
            this.show  = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        resolve(confirmed) {
            this.show = false;
            this._resolve?.(confirmed);
            this.table    = null;
            this._resolve = null;
        },
    });

    Alpine.store('sizeModal', {
        show:     false,
        label:    '',
        isShrink: false,
        _resolve: null,

        prompt(label, isShrink) {
            this.label    = label;
            this.isShrink = isShrink;
            this.show     = true;
            return new Promise(resolve => { this._resolve = resolve; });
        },

        resolve(confirmed) {
            this.show     = false;
            this._resolve?.(confirmed);
            this._resolve = null;
        },
    });

    Alpine.data('tableMap', () => {
        const init = readMapInit();
        const urls = readMapUrls();

        return {
            tables:                init.tables           ?? [],
            elements:              init.elements          ?? [],
            zones:                 init.zones             ?? [],
            floorWidth:            init.floorWidth        ?? 1200,
            floorHeight:           init.floorHeight       ?? 800,
            floorsEnabled:         init.floorsEnabled     ?? false,
            floorCount:            init.floorCount        ?? 1,
            floorCanvasSizes:      init.floorCanvasSizes  ?? {},
            currentFloor:          1,
            currentView:           'floor',
            readonly:              init.readonly          ?? false,
            editMode:              false,
            canvasZoom:            1,
            isDraggingFromPalette: false,
            currentDragShape:      null,
            isDraggingZone:        false,
            editingTableId:        null,
            editingTable:          null,
            editPanelPos:          { x: 0, y: 0 },
            editingZoneId:         null,
            editingZone:           null,
            editZonePanelPos:      { x: 0, y: 0 },
            _editBtnEl:            null,
            _zoneBtnEl:            null,
            isRotating:            false,
            rotatingId:            null,
            draggingId:            null,
            resizingId:            null,
            hoveredId:             null,
            selectedId:            null,
            isRotatingZone:        false,
            zoneColor:             '#6366f1',
            toast:                 { show: false, msg: '', error: false, _timer: null },
            rotTooltip:            { show: false, x: 0, y: 0, deg: 0 },
            undoStack:             [],
            redoStack:             [],
            clipboard:             null,
            isPanning:             false,
            focusedVertexIdx:      null,
            contextMenu:           { show: false, x: 0, y: 0, type: null, item: null },
            _init:                 init,
            _urls:                 urls,

            init() {
                this.$nextTick(() => {
                    if (!this.readonly) {
                        this.initTableInteract();
                        this.initZoneInteract();
                        this.initPaletteInteract();
                        this.clampAllToCanvas();
                    }
                    this._applyOverviewZoom();
                    this._prefetchQrSvgs();
                });
                this.pollStatuses();
                setInterval(() => this.pollStatuses(), 5000);
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') this.pollStatuses();
                });
                if (!window._zampaResizeListener) {
                    window._zampaResizeListener = () => { if (!this.editMode) this._applyOverviewZoom(); };
                    window.addEventListener('resize', window._zampaResizeListener);
                }
                if (!window._zampaKbListener) {
                    window._zampaKbListener = (e) => {
                        if (this._isTyping()) return;
                        if (e.ctrlKey && (e.key === 'c' || e.key === 'C')) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.copySelected();
                        } else if (e.ctrlKey && (e.key === 'v' || e.key === 'V')) {
                            e.preventDefault();
                            e.stopPropagation();
                            this.pasteSelected();
                        }
                    };
                    document.addEventListener('keydown', window._zampaKbListener, true);
                }
            },

            _prefetchQrSvgs() {
                const store = Alpine.store('viewPanel');
                this.tables
                    .filter(t => t.is_service_point !== false)
                    .forEach(table => {
                        fetch(`/mesas/${table.id}/qr?h=${table.unique_hash}`)
                            .then(r => r.text())
                            .then(svg => { store._qrCache[table.id] = svg; })
                            .catch(() => {});
                    });
            },

            canvasBounds(item, w, h) {
                return canvasBoundsFor(item, w ?? this.floorWidth, h ?? this.floorHeight);
            },

            sizeForItem(item) {
                const floor = this.floorsEnabled ? (item.floor ?? 1) : 1;
                return this.floorCanvasSizes[floor] ?? { width: this.floorWidth, height: this.floorHeight };
            },

            async clampAllToCanvas() {
                for (const item of [...this.tables, ...this.elements]) {
                    const { width: w, height: h } = this.sizeForItem(item);
                    const { minX, maxX, minY, maxY } = this.canvasBounds(item, w, h);
                    const cx = Math.max(minX, Math.min(maxX, item.position_x));
                    const cy = Math.max(minY, Math.min(maxY, item.position_y));
                    if (cx !== item.position_x || cy !== item.position_y) {
                        await this.persistPosition(item.id, cx, cy, item.width, item.height);
                    }
                }
                for (const zone of this.zones) {
                    const { width: w, height: h } = this.sizeForItem(zone);
                    const maxX = Math.max(0, w - zone.width);
                    const maxY = Math.max(0, h - zone.height);
                    const cx   = Math.max(0, Math.min(maxX, zone.position_x));
                    const cy   = Math.max(0, Math.min(maxY, zone.position_y));
                    if (cx !== zone.position_x || cy !== zone.position_y) {
                        zone.position_x = cx;
                        zone.position_y = cy;
                        await this.persistZonePosition(zone.id, cx, cy);
                    }
                }
            },

            async pollStatuses() {
                try {
                    const res = await fetch(this._urls.statuses, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    data.forEach(s => {
                        const t = this.tables.find(t => t.id === s.id);
                        if (t) t.orderStatus = s.orderStatus;
                    });
                } catch {}
            },

            async requestCanvasSize(w, h, label) {
                const cur      = this.floorCanvasSizes[this.floorsEnabled ? this.currentFloor : 1]
                                 ?? { width: this.floorWidth, height: this.floorHeight };
                if (w === cur.width && h === cur.height) return;
                const isShrink = w < cur.width || h < cur.height;
                const confirmed = await Alpine.store('sizeModal').prompt(label, isShrink);
                if (!confirmed) return;
                this.setCanvasSize(w, h);
            },

            async setCanvasSize(w, h) {
                this.pushUndo();
                const floor    = this.floorsEnabled ? this.currentFloor : 1;
                const prevW    = this.floorWidth;
                const prevH    = this.floorHeight;
                const prevSize = this.floorCanvasSizes[floor] ? { ...this.floorCanvasSizes[floor] } : { width: prevW, height: prevH };

                this.floorWidth  = w;
                this.floorHeight = h;
                this.floorCanvasSizes[floor] = { width: w, height: h };

                const snapshots = [];
                if (w < prevW || h < prevH) {
                    for (const item of [...this.tables, ...this.elements]) {
                        const { width: sw, height: sh } = this.sizeForItem(item);
                        const { minX, maxX, minY, maxY } = this.canvasBounds(item, sw, sh);
                        const cx = Math.max(minX, Math.min(maxX, item.position_x));
                        const cy = Math.max(minY, Math.min(maxY, item.position_y));
                        if (cx !== item.position_x || cy !== item.position_y) {
                            snapshots.push({ item, prevX: item.position_x, prevY: item.position_y });
                            item.position_x = cx;
                            item.position_y = cy;
                        }
                    }
                    for (const zone of this.zones) {
                        const { width: sw, height: sh } = this.sizeForItem(zone);
                        const maxX = Math.max(0, sw - zone.width);
                        const maxY = Math.max(0, sh - zone.height);
                        const cx   = Math.max(0, Math.min(maxX, zone.position_x));
                        const cy   = Math.max(0, Math.min(maxY, zone.position_y));
                        if (cx !== zone.position_x || cy !== zone.position_y) {
                            snapshots.push({ item: zone, prevX: zone.position_x, prevY: zone.position_y, isZone: true });
                            zone.position_x = cx;
                            zone.position_y = cy;
                        }
                    }
                }

                try {
                    const res = await fetch(this._urls.canvasUpdate, {
                        method:  'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept':       'application/json',
                        },
                        body: JSON.stringify({ floor_width: w, floor_height: h, floor }),
                    });

                    const json = await res.json();

                    if (!res.ok || !json.success) {
                        this.floorWidth  = prevW;
                        this.floorHeight = prevH;
                        this.floorCanvasSizes[floor] = prevSize;
                        for (const { item, prevX, prevY } of snapshots) {
                            item.position_x = prevX;
                            item.position_y = prevY;
                        }
                        this.showToast(json.message ?? 'Error al guardar el tamaño.', true);
                        return;
                    }

                    this.showToast(`Plano ${w} × ${h} px guardado.`);
                    for (const { item, isZone } of snapshots) {
                        if (isZone) {
                            await this.persistZonePosition(item.id, item.position_x, item.position_y);
                        } else {
                            await this.persistPosition(item.id, item.position_x, item.position_y, item.width, item.height);
                        }
                    }
                } catch {
                    this.floorWidth  = prevW;
                    this.floorHeight = prevH;
                    this.floorCanvasSizes[floor] = prevSize;
                    for (const { item, prevX, prevY } of snapshots) {
                        item.position_x = prevX;
                        item.position_y = prevY;
                    }
                    this.showToast('Error de red al guardar el tamaño.', true);
                }
            },

            showToast(msg, error = false) {
                clearTimeout(this.toast._timer);
                this.toast = { show: true, msg, error, _timer: null };
                this.toast._timer = setTimeout(() => { this.toast.show = false; }, 3000);
            },

            rectCorners(item)          { return rectCorners(item); },
            projectOnAxis(corners, ax) { return projectOnAxis(corners, ax); },
            obbOverlaps(a, b)          { return obbOverlaps(a, b); },
            circleObbOverlaps(c, r)    { return circleObbOverlaps(c, r); },
            overlaps(a, b)             { return overlaps(a, b); },
            segmentsIntersect(p1, p2, p3, p4) { return segmentsIntersect(p1, p2, p3, p4); },
            pointInPolygon(pt, poly)   { return pointInPolygon(pt, poly); },
            polygonWorldCorners(zone)  { return polygonWorldCorners(zone); },
            polygonOverlaps(pa, pb)    { return polygonOverlaps(pa, pb); },

            hasZoneCollision(zone) {
                if (this.floorsEnabled && this.currentView === 'general') return false;
                const selfId    = zone.id ?? null;
                const zoneFloor = this.floorsEnabled ? (zone.floor ?? this.currentFloor) : null;
                const sameFloor = (z) => !this.floorsEnabled || (z.floor ?? 1) === (zoneFloor ?? 1);
                const cornersA  = polygonWorldCorners(zone);
                return this.zones.filter(sameFloor).some(z => {
                    if (z.id === selfId) return false;
                    return polygonOverlaps(cornersA, polygonWorldCorners(z));
                });
            },

            hasCollision(item) {
                if (this.floorsEnabled && this.currentView === 'general') return false;
                const isSpecial = ['bar', 'stool', 'chair', 'fireplace', 'pillar', 'column'].includes(item.shape);
                const selfId    = item.id ?? null;
                const itemFloor = this.floorsEnabled ? (item.floor ?? this.currentFloor) : null;
                const sameFloor = (other) => !this.floorsEnabled || (other.floor ?? 1) === (itemFloor ?? 1);
                if (isSpecial) {
                    return this.tables.filter(sameFloor).some(t => overlaps(item, t)) ||
                           this.elements.filter(sameFloor).some(e => e.id !== selfId && overlaps(item, e));
                }
                return this.tables.filter(sameFloor).some(t => t.id !== selfId && overlaps(item, t)) ||
                       this.elements.filter(sameFloor).some(e => overlaps(item, e));
            },

            snapshot() {
                return {
                    floorWidth:       this.floorWidth,
                    floorHeight:      this.floorHeight,
                    floorCanvasSizes: JSON.parse(JSON.stringify(this.floorCanvasSizes)),
                    canvasZoom:       this.canvasZoom,
                    tables:   this.tables.map(t  => ({ id: t.id,  position_x: t.position_x,  position_y: t.position_y,  width: t.width,  height: t.height,  rotation: t.rotation  ?? 0 })),
                    elements: this.elements.map(e => ({ id: e.id,  position_x: e.position_x,  position_y: e.position_y,  width: e.width,  height: e.height,  rotation: e.rotation  ?? 0, vertices: e.vertices ? JSON.parse(JSON.stringify(e.vertices)) : null })),
                    zones:    this.zones.map(z    => ({ id: z.id,  position_x: z.position_x,  position_y: z.position_y,  width: z.width,  height: z.height,  rotation: z.rotation  ?? 0, color: z.color, vertices: z.vertices ? JSON.parse(JSON.stringify(z.vertices)) : null })),
                };
            },

            pushUndo() {
                this.undoStack.push(this.snapshot());
                this.redoStack = [];
                if (this.undoStack.length > 50) this.undoStack.shift();
            },

            async undo() {
                if (!this.undoStack.length) return;
                this.redoStack.push(this.snapshot());
                await this._applySnapshot(this.undoStack.pop());
            },

            async redo() {
                if (!this.redoStack.length) return;
                this.undoStack.push(this.snapshot());
                await this._applySnapshot(this.redoStack.pop());
            },

            async _applySnapshot(snap) {
                const floor = this.floorsEnabled ? this.currentFloor : 1;
                const canvasSizeChanged =
                    snap.floorWidth  !== this.floorWidth  ||
                    snap.floorHeight !== this.floorHeight  ||
                    JSON.stringify(snap.floorCanvasSizes) !== JSON.stringify(this.floorCanvasSizes);

                this.canvasZoom       = snap.canvasZoom;
                this.floorWidth       = snap.floorWidth;
                this.floorHeight      = snap.floorHeight;
                this.floorCanvasSizes = snap.floorCanvasSizes;

                const toPersistItems = [];
                const toPersistZones = [];

                for (const s of snap.tables) {
                    const t = this.tables.find(t => t.id === s.id);
                    if (!t) continue;
                    if (t.position_x !== s.position_x || t.position_y !== s.position_y ||
                        t.width !== s.width || t.height !== s.height || (t.rotation ?? 0) !== s.rotation) {
                        t.position_x = s.position_x; t.position_y = s.position_y;
                        t.width      = s.width;       t.height     = s.height;
                        t.rotation   = s.rotation;
                        toPersistItems.push(t);
                    }
                }
                for (const s of snap.elements) {
                    const e = this.elements.find(e => e.id === s.id);
                    if (!e) continue;
                    const posChg  = e.position_x !== s.position_x || e.position_y !== s.position_y ||
                                    e.width !== s.width || e.height !== s.height || (e.rotation ?? 0) !== s.rotation;
                    const vertChg = JSON.stringify(e.vertices ?? null) !== JSON.stringify(s.vertices ?? null);
                    if (posChg || vertChg) {
                        e.position_x = s.position_x; e.position_y = s.position_y;
                        e.width      = s.width;       e.height     = s.height;
                        e.rotation   = s.rotation;
                        if (vertChg) e.vertices = s.vertices ? JSON.parse(JSON.stringify(s.vertices)) : null;
                        toPersistItems.push({ item: e, vertChg });
                    }
                }
                for (const s of snap.zones) {
                    const z = this.zones.find(z => z.id === s.id);
                    if (!z) continue;
                    const posChg  = z.position_x !== s.position_x || z.position_y !== s.position_y ||
                                    z.width !== s.width || z.height !== s.height ||
                                    (z.rotation ?? 0) !== s.rotation || z.color !== s.color;
                    const vertChg = JSON.stringify(z.vertices ?? null) !== JSON.stringify(s.vertices ?? null);
                    if (posChg || vertChg) {
                        z.position_x = s.position_x; z.position_y = s.position_y;
                        z.width      = s.width;       z.height     = s.height;
                        z.rotation   = s.rotation;    z.color      = s.color;
                        if (vertChg) z.vertices = s.vertices ? JSON.parse(JSON.stringify(s.vertices)) : null;
                        toPersistZones.push({ zone: z, vertChg });
                    }
                }

                if (canvasSizeChanged) {
                    try {
                        await fetch(this._urls.canvasUpdate, {
                            method:  'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ floor_width: snap.floorWidth, floor_height: snap.floorHeight, floor }),
                        });
                    } catch {}
                }
                for (const { item, vertChg } of toPersistItems) {
                    await this.persistPosition(item.id, item.position_x, item.position_y, item.width, item.height);
                    if (vertChg && item.vertices) await this.persistBarVertices(item.id, item.vertices);
                }
                for (const { zone: z, vertChg } of toPersistZones) {
                    try {
                        await fetch(`/zonas/${z.id}`, {
                            method:  'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ position_x: z.position_x, position_y: z.position_y, width: z.width, height: z.height, rotation: z.rotation }),
                        });
                    } catch {}
                    if (vertChg) await this.persistZoneVertices(z.id, z.vertices ?? []);
                }
            },

            initTableInteract() {
                interact('.table-item').unset();
                interact('.table-item')
                    .draggable({
                        ignoreFrom:  '.rotation-handle, .resize-handle, .bar-vertex-handle',
                        inertia:    false,
                        autoScroll: true,
                        listeners: {
                            start: (event) => {
                                if (this.floorsEnabled && this.currentView === 'general') { event.interaction.stop(); return; }
                                if (!this.editMode) { event.interaction.stop(); return; }
                                this.pushUndo();
                                const el   = event.target;
                                const id   = parseInt(el.dataset.tableId);
                                const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
                                this.closeEditPanels();
                                this.draggingId    = id;
                                el._dragStartX     = item?.position_x;
                                el._dragStartY     = item?.position_y;
                            },
                            move: (event) => {
                                const el   = event.target;
                                const id   = parseInt(el.dataset.tableId);
                                const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
                                const curX = parseFloat(el.style.left) || 0;
                                const curY = parseFloat(el.style.top)  || 0;
                                const { minX, maxX, minY, maxY } = item
                                    ? this.canvasBounds(item)
                                    : { minX: 0, maxX: this.floorWidth - 100, minY: 0, maxY: this.floorHeight - 100 };
                                const propX = Math.max(minX, Math.min(maxX, Math.round(curX + event.dx / this.canvasZoom)));
                                const propY = Math.max(minY, Math.min(maxY, Math.round(curY + event.dy / this.canvasZoom)));
                                if (item) { item.position_x = propX; item.position_y = propY; }
                                el.style.left = `${propX}px`;
                                el.style.top  = `${propY}px`;
                            },
                            end: (event) => {
                                const el   = event.target;
                                const id   = parseInt(el.dataset.tableId);
                                const x    = Math.round(parseFloat(el.style.left) || 0);
                                const y    = Math.round(parseFloat(el.style.top)  || 0);
                                const w    = Math.round(parseFloat(el.style.width)  || 100);
                                const h    = Math.round(parseFloat(el.style.height) || 100);
                                const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
                                this.draggingId = null;
                                if (item && this.hasCollision(item)) {
                                    const origX = el._dragStartX ?? x;
                                    const origY = el._dragStartY ?? y;
                                    item.position_x = origX;
                                    item.position_y = origY;
                                    el.style.left   = `${origX}px`;
                                    el.style.top    = `${origY}px`;
                                    this.undoStack.pop();
                                    this.showToast('No se puede superponer con otra mesa.', true);
                                    this.persistPosition(id, origX, origY, w, h);
                                    return;
                                }
                                this.persistPosition(id, x, y, w, h);
                            },
                        },
                    });
            },

            reinitInteract() {
                this.$nextTick(() => this.initTableInteract());
            },

            initZoneInteract() {
                interact('.zone-item').unset();
            },

            reinitZoneInteract() {
                this.$nextTick(() => this.initZoneInteract());
            },

            startZoneDrag(event, zone) {
                if (!this.editMode) return;
                this.pushUndo();
                const startMX = event.clientX;
                const startMY = event.clientY;
                const startPx = zone.position_x;
                const startPy = zone.position_y;
                const zoneEl  = this.$refs.canvas.querySelector(`[data-zone-id="${zone.id}"]`);
                this.closeEditPanels();
                this.draggingId            = zone.id;
                document.body.style.cursor = 'grabbing';
                let curX = startPx, curY = startPy;
                let latestMX = event.clientX, latestMY = event.clientY;
                let rafPending = false;
                const processFrame = () => {
                    rafPending = false;
                    const maxX = Math.max(0, this.floorWidth  - zone.width);
                    const maxY = Math.max(0, this.floorHeight - zone.height);
                    curX = Math.max(0, Math.min(maxX, startPx + (latestMX - startMX) / this.canvasZoom));
                    curY = Math.max(0, Math.min(maxY, startPy + (latestMY - startMY) / this.canvasZoom));
                    if (zoneEl) { zoneEl.style.left = `${curX}px`; zoneEl.style.top = `${curY}px`; }
                };
                const onMove = (e) => {
                    latestMX = e.clientX; latestMY = e.clientY;
                    if (!rafPending) { rafPending = true; requestAnimationFrame(processFrame); }
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    document.body.style.cursor = '';
                    curX = Math.round(curX); curY = Math.round(curY);
                    zone.position_x = curX;
                    zone.position_y = curY;
                    this.draggingId = null;
                    if (this.hasZoneCollision(zone)) {
                        this.showToast('No se puede colocar aquí: la zona colisiona con otra zona.', true);
                        this.undoStack.pop();
                        zone.position_x = startPx;
                        zone.position_y = startPy;
                        if (zoneEl) { zoneEl.style.left = `${startPx}px`; zoneEl.style.top = `${startPy}px`; }
                        return;
                    }
                    await this.persistZonePosition(zone.id, curX, curY);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            startZoneRotation(event, zone) {
                if (!this.editMode) return;
                this.pushUndo();
                const ROTATE_CURSOR = "url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grabbing";
                const canvasRect = this.$refs.canvas.getBoundingClientRect();
                const centerX    = canvasRect.left + (zone.position_x + zone.width  / 2) * this.canvasZoom;
                const centerY    = canvasRect.top  + (zone.position_y + zone.height / 2) * this.canvasZoom;
                this.closeEditPanels();
                this.isRotating            = true;
                this.rotatingId            = zone.id;
                this.rotTooltip.show       = true;
                document.body.style.cursor = ROTATE_CURSOR;
                let lastValidRotation = zone.rotation ?? 0;
                const onMove = (e) => {
                    const dx       = e.clientX - centerX;
                    const dy       = e.clientY - centerY;
                    let   angle    = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
                    angle = ((angle % 360) + 360) % 360;
                    const proposed = Math.round(angle);
                    const testItem = { ...zone, rotation: proposed };
                    if (!this.hasZoneCollision(testItem)) {
                        zone.rotation     = proposed;
                        lastValidRotation = proposed;
                    } else {
                        zone.rotation = lastValidRotation;
                    }
                    this.rotTooltip.x   = e.clientX;
                    this.rotTooltip.y   = e.clientY;
                    this.rotTooltip.deg = zone.rotation;
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    this.isRotating            = false;
                    this.rotatingId            = null;
                    this.rotTooltip.show       = false;
                    document.body.style.cursor = '';
                    await this.persistZoneRotation(zone.id, zone.rotation ?? 0);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            startElementDrag(event, element) {
                if (this.floorsEnabled && this.currentView === 'general') return;
                if (!this.editMode) return;
                this.pushUndo();
                const startPx = element.position_x;
                const startPy = element.position_y;
                const startMX = event.clientX;
                const startMY = event.clientY;
                this.closeEditPanels();
                this.draggingId            = element.id;
                document.body.style.cursor = 'grabbing';
                const onMove = (e) => {
                    const { minX, maxX, minY, maxY } = this.canvasBounds(element);
                    element.position_x = Math.max(minX, Math.min(maxX, Math.round(startPx + (e.clientX - startMX) / this.canvasZoom)));
                    element.position_y = Math.max(minY, Math.min(maxY, Math.round(startPy + (e.clientY - startMY) / this.canvasZoom)));
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    this.draggingId            = null;
                    document.body.style.cursor = '';
                    if (this.hasCollision(element)) {
                        element.position_x = startPx;
                        element.position_y = startPy;
                        this.undoStack.pop();
                        this.showToast('No se puede superponer con otro elemento.', true);
                        await this.persistPosition(element.id, startPx, startPy, element.width, element.height);
                    } else {
                        await this.persistPosition(element.id, element.position_x, element.position_y, element.width, element.height);
                    }
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            initPaletteInteract() {
                const ghost     = document.getElementById('palette-ghost');
                const canvasEl  = this.$refs.canvas;
                let   dropShape = null;
                let   dropW     = 100;
                let   dropH     = 100;
                let   dropX     = 0;
                let   dropY     = 0;

                interact('.palette-item').draggable({
                    inertia: false,
                    listeners: {
                        start: (event) => {
                            if (this.floorsEnabled && this.currentView === 'general') { event.interaction.stop(); return; }
                            if (this.tables.length >= this._init.maxTables) {
                                this.showToast(`Límite de ${this._init.maxTables} mesas alcanzado.`, true);
                                return;
                            }
                            dropShape = event.target.dataset.shape;
                            dropW     = parseInt(event.target.dataset.width)  || 100;
                            dropH     = parseInt(event.target.dataset.height) || 100;
                            ghost.style.width   = `${dropW}px`;
                            ghost.style.height  = `${dropH}px`;
                            ghost.style.borderRadius =
                                dropShape === 'round' ? '9999px' :
                                dropShape === 'square' ? '12px' : '8px';
                            ghost.querySelector('span').textContent = 'Nueva mesa';
                            ghost.classList.remove('hidden');
                            ghost.classList.add('flex');
                            this.isDraggingFromPalette = true;
                            this.currentDragShape      = dropShape;
                        },
                        move: (event) => {
                            const canvasRect = canvasEl.getBoundingClientRect();
                            const cx         = event.clientX;
                            const cy         = event.clientY;
                            ghost.style.left = `${cx - dropW / 2}px`;
                            ghost.style.top  = `${cy - dropH / 2}px`;
                            dropX = Math.max(0, Math.round((cx - canvasRect.left - dropW / 2) / this.canvasZoom));
                            dropY = Math.max(0, Math.round((cy - canvasRect.top  - dropH / 2) / this.canvasZoom));
                        },
                        end: async (event) => {
                            ghost.classList.add('hidden');
                            ghost.classList.remove('flex');
                            this.isDraggingFromPalette = false;
                            this.currentDragShape      = null;
                            const canvasRect = canvasEl.getBoundingClientRect();
                            const cx         = event.clientX;
                            const cy         = event.clientY;
                            const overCanvas =
                                cx >= canvasRect.left && cx <= canvasRect.right &&
                                cy >= canvasRect.top  && cy <= canvasRect.bottom;
                            if (!overCanvas) return;
                            const candidate = { position_x: dropX, position_y: dropY, width: dropW, height: dropH, shape: dropShape, id: null };
                            if (this.hasCollision(candidate)) {
                                this.showToast('No se puede colocar aquí: colisiona con otra mesa o elemento.', true);
                                return;
                            }
                            const name = await Alpine.store('tableModal').prompt('table');
                            if (name === null) return;
                            await this.createTable(name || null, dropShape, dropX, dropY, dropW, dropH);
                        },
                    },
                });

                interact('.special-item').draggable({
                    inertia: false,
                    listeners: {
                        start: (event) => {
                            if (this.floorsEnabled && this.currentView === 'general') { event.interaction.stop(); return; }
                            const shape = event.target.dataset.shape;
                            const dW    = parseInt(event.target.dataset.width)  || 80;
                            const dH    = parseInt(event.target.dataset.height) || 50;
                            ghost.style.width        = `${dW}px`;
                            ghost.style.height       = `${dH}px`;
                            ghost.style.borderRadius = shape === 'stool' ? '9999px' : '8px';
                            const isGray = shape === 'pillar' || shape === 'column';
                            const isRed  = shape === 'fireplace';
                            ghost.style.borderColor  = shape === 'bar' ? '#d97706' : isRed ? '#b91c1c' : isGray ? '#6b7280' : '#4ade80';
                            ghost.style.background   = shape === 'bar' ? 'rgba(251,191,36,0.3)' : isRed ? 'rgba(185,28,28,0.15)' : isGray ? 'rgba(107,114,128,0.2)' : 'rgba(74,222,128,0.2)';
                            const nameMap = { bar: 'Nueva barra', chair: 'Nueva silla', fireplace: 'Nueva chimenea', pillar: 'Nuevo pilar', column: 'Nueva columna' };
                            ghost.querySelector('span').textContent = nameMap[shape] ?? 'Nuevo elemento';
                            ghost.classList.remove('hidden');
                            ghost.classList.add('flex');
                            this.isDraggingFromPalette = true;
                            this.currentDragShape      = shape;
                        },
                        move: (event) => {
                            const w = parseInt(event.target.dataset.width)  || 80;
                            const h = parseInt(event.target.dataset.height) || 50;
                            ghost.style.left = `${event.clientX - w / 2}px`;
                            ghost.style.top  = `${event.clientY - h / 2}px`;
                            const rect = canvasEl.getBoundingClientRect();
                            dropX = Math.max(0, Math.round((event.clientX - rect.left - w / 2) / this.canvasZoom));
                            dropY = Math.max(0, Math.round((event.clientY - rect.top  - h / 2) / this.canvasZoom));
                            dropShape = event.target.dataset.shape;
                            dropW     = w;
                            dropH     = h;
                        },
                        end: async (event) => {
                            ghost.classList.add('hidden');
                            ghost.classList.remove('flex');
                            ghost.style.borderColor = '';
                            ghost.style.background  = '';
                            this.isDraggingFromPalette = false;
                            this.currentDragShape      = null;
                            const rect = canvasEl.getBoundingClientRect();
                            const over = event.clientX >= rect.left && event.clientX <= rect.right &&
                                         event.clientY >= rect.top  && event.clientY <= rect.bottom;
                            if (!over) return;
                            const candidate = { position_x: dropX, position_y: dropY, width: dropW, height: dropH, shape: dropShape, id: null };
                            if (this.hasCollision(candidate)) {
                                this.showToast('No se puede colocar aquí: colisiona con otra mesa o elemento.', true);
                                return;
                            }
                            const dropNames = { bar: 'Barra', chair: 'Silla', stool: 'Taburete', fireplace: 'Chimenea', pillar: 'Pilar', column: 'Columna' };
                            const name = dropNames[dropShape] ?? 'Elemento';
                            await this.createSpecialElement(name, dropShape, dropX, dropY, dropW, dropH);
                        },
                    },
                });

                interact('.zone-palette-item').draggable({
                    inertia: false,
                    listeners: {
                        start: (event) => {
                            if (this.floorsEnabled && this.currentView === 'general') { event.interaction.stop(); return; }
                            const w = parseInt(event.target.dataset.width)  || 300;
                            const h = parseInt(event.target.dataset.height) || 200;
                            ghost.style.width        = `${w}px`;
                            ghost.style.height       = `${h}px`;
                            ghost.style.borderRadius = '8px';
                            ghost.style.borderColor  = this.zoneColor;
                            ghost.style.background   = this.zoneColor + '33';
                            ghost.querySelector('span').textContent = 'Nueva zona';
                            ghost.classList.remove('hidden');
                            ghost.classList.add('flex');
                            this.isDraggingZone = true;
                        },
                        move: (event) => {
                            const w = parseInt(event.target.dataset.width)  || 300;
                            const h = parseInt(event.target.dataset.height) || 200;
                            ghost.style.left = `${event.clientX - w / 2}px`;
                            ghost.style.top  = `${event.clientY - h / 2}px`;
                            const rect = canvasEl.getBoundingClientRect();
                            dropX = Math.max(0, Math.round((event.clientX - rect.left - w / 2) / this.canvasZoom));
                            dropY = Math.max(0, Math.round((event.clientY - rect.top  - h / 2) / this.canvasZoom));
                            dropW = w;
                            dropH = h;
                        },
                        end: async (event) => {
                            ghost.classList.add('hidden');
                            ghost.classList.remove('flex');
                            ghost.style.borderColor = '';
                            ghost.style.background  = '';
                            this.isDraggingZone = false;
                            const rect = canvasEl.getBoundingClientRect();
                            const over = event.clientX >= rect.left && event.clientX <= rect.right &&
                                         event.clientY >= rect.top  && event.clientY <= rect.bottom;
                            if (!over) return;
                            const name = await Alpine.store('tableModal').prompt('zone');
                            if (!name) return;
                            const candidate = { position_x: dropX, position_y: dropY, width: dropW, height: dropH, rotation: 0, id: null, floor: this.floorsEnabled ? this.currentFloor : 1 };
                            if (this.hasZoneCollision(candidate)) {
                                this.showToast('No se puede colocar aquí: la zona colisiona con otra zona.', true);
                                return;
                            }
                            await this.createZone(name, this.zoneColor, dropX, dropY, dropW, dropH);
                        },
                    },
                });
            },

            async createTable(name, shape, x, y, w, h, rotation = 0) {
                try {
                    const res = await fetch(this._urls.store, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ name: name || null, shape, position_x: x, position_y: y, width: w, height: h, rotation, is_service_point: true, floor: this.floorsEnabled ? this.currentFloor : 1 }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al crear la mesa.', true); return; }
                    this.tables.push(json.data);
                    this.reinitInteract();
                    this.showToast(json.message);
                } catch {
                    this.showToast('Error de red al crear la mesa.', true);
                }
            },

            async createSpecialElement(name, shape, x, y, w, h, rotation = 0) {
                try {
                    const res = await fetch(this._urls.store, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ name, shape, position_x: x, position_y: y, width: w, height: h, rotation, is_service_point: false, floor: this.floorsEnabled ? this.currentFloor : 1 }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al crear el elemento.', true); return; }
                    this.elements.push(json.data);
                    this.reinitInteract();
                    this.showToast(json.message);
                } catch {
                    this.showToast('Error de red al crear el elemento.', true);
                }
            },

            async createZone(name, color, x, y, w, h) {
                try {
                    const res = await fetch(this._urls.zonesStore, {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ name, color, position_x: x, position_y: y, width: w, height: h, floor: this.floorsEnabled ? this.currentFloor : 1 }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al crear la zona.', true); return; }
                    this.zones.push(json.data);
                    this.reinitZoneInteract();
                    this.showToast(json.message);
                } catch {
                    this.showToast('Error de red al crear la zona.', true);
                }
            },

            async updateZoneName(zone, name) {
                name = name.trim();
                if (!name || name === zone.name) return;
                const prev = zone.name;
                zone.name  = name;
                try {
                    const res = await fetch(`/zonas/${zone.id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ name }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { zone.name = prev; this.showToast(json.message ?? 'Error.', true); return; }
                    this.showToast(json.message);
                } catch { zone.name = prev; this.showToast('Error de red.', true); }
            },

            async updateZoneColor(zone, color) {
                const prev = zone.color;
                zone.color = color;
                try {
                    const res = await fetch(`/zonas/${zone.id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ color }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { zone.color = prev; this.showToast(json.message ?? 'Error.', true); }
                } catch { zone.color = prev; this.showToast('Error de red.', true); }
            },

            async persistZonePosition(id, x, y) {
                try {
                    const res = await fetch(`/zonas/${id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ position_x: x, position_y: y }),
                    });
                    if (!res.ok) this.showToast('Error al guardar la posición de la zona.', true);
                } catch { this.showToast('Error de red.', true); }
            },

            async persistZoneRotation(id, rotation) {
                try {
                    const res = await fetch(`/zonas/${id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ rotation }),
                    });
                    if (!res.ok) this.showToast('Error al guardar la rotación de la zona.', true);
                } catch { this.showToast('Error de red.', true); }
            },

            vertexPoints(item) {
                if (!item.vertices || item.vertices.length < 3) return '';
                return item.vertices.map(v => `${v.x},${v.y}`).join(' ');
            },

            startVertexDrag(event, zone, idx) {
                if (!this.editMode) return;
                event.stopPropagation();
                this.pushUndo();
                const startMX = event.clientX;
                const startMY = event.clientY;
                const startVX = zone.vertices[idx].x;
                const startVY = zone.vertices[idx].y;
                const handle  = event.currentTarget;
                const zoneEl  = handle.closest('[data-zone-id]');
                const svgPoly = zoneEl?.querySelector('polygon');
                let newX = startVX, newY = startVY;
                const onMove = (e) => {
                    newX = startVX + (e.clientX - startMX) / this.canvasZoom;
                    newY = startVY + (e.clientY - startMY) / this.canvasZoom;
                    handle.style.left = `${newX - 6}px`;
                    handle.style.top  = `${newY - 6}px`;
                    if (svgPoly) {
                        svgPoly.setAttribute('points',
                            zone.vertices.map((v, i) => i === idx ? `${newX},${newY}` : `${v.x},${v.y}`).join(' ')
                        );
                    }
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    zone.vertices[idx].x = newX;
                    zone.vertices[idx].y = newY;
                    await this.persistZoneVertices(zone.id, zone.vertices);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            startBarVertexDrag(event, element, idx) {
                if (!this.editMode) return;
                event.stopPropagation();
                this.pushUndo();
                const startMX = event.clientX;
                const startMY = event.clientY;
                const startVX = element.vertices[idx].x;
                const startVY = element.vertices[idx].y;
                const handle  = event.currentTarget;
                const barEl   = handle.closest('[data-table-id]');
                const svgPoly = barEl?.querySelector('polygon');
                let newX = startVX, newY = startVY;
                const onMove = (e) => {
                    newX = startVX + (e.clientX - startMX) / this.canvasZoom;
                    newY = startVY + (e.clientY - startMY) / this.canvasZoom;
                    handle.style.left = `${newX - 6}px`;
                    handle.style.top  = `${newY - 6}px`;
                    if (svgPoly) {
                        svgPoly.setAttribute('points',
                            element.vertices.map((v, i) => i === idx ? `${newX},${newY}` : `${v.x},${v.y}`).join(' ')
                        );
                    }
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    element.vertices[idx].x = newX;
                    element.vertices[idx].y = newY;
                    await this.persistBarVertices(element.id, element.vertices);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            async persistZoneVertices(id, vertices) {
                try {
                    const res = await fetch(`/zonas/${id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ vertices }),
                    });
                    if (!res.ok) this.showToast('Error al guardar los vértices.', true);
                } catch { this.showToast('Error de red.', true); }
            },

            async persistBarVertices(id, vertices) {
                try {
                    const res = await fetch(`/mesas/${id}/vertices`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ vertices }),
                    });
                    if (!res.ok) this.showToast('Error al guardar los vértices de la barra.', true);
                } catch { this.showToast('Error de red.', true); }
            },

            initPolygonVertices(zone) {
                if (zone.vertices && zone.vertices.length >= 3) return;
                zone.vertices = [
                    { x: 0,           y: 0            },
                    { x: zone.width,  y: 0            },
                    { x: zone.width,  y: zone.height  },
                    { x: 0,           y: zone.height  },
                ];
                this.persistZoneVertices(zone.id, zone.vertices);
            },

            initBarPolygonVertices(element) {
                if (element.vertices && element.vertices.length >= 3) return;
                element.vertices = [
                    { x: 0,              y: 0               },
                    { x: element.width,  y: 0               },
                    { x: element.width,  y: element.height  },
                    { x: 0,              y: element.height  },
                ];
                this.persistBarVertices(element.id, element.vertices);
            },

            addZoneVertex(zone, edgeStartIdx) {
                this.pushUndo();
                const n = zone.vertices.length;
                const a = zone.vertices[edgeStartIdx];
                const b = zone.vertices[(edgeStartIdx + 1) % n];
                zone.vertices.splice(edgeStartIdx + 1, 0, { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 });
                this.persistZoneVertices(zone.id, zone.vertices);
            },

            addBarVertex(element, edgeStartIdx) {
                this.pushUndo();
                const n = element.vertices.length;
                const a = element.vertices[edgeStartIdx];
                const b = element.vertices[(edgeStartIdx + 1) % n];
                element.vertices.splice(edgeStartIdx + 1, 0, { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 });
                this.persistBarVertices(element.id, element.vertices);
            },

            removeZoneVertex(zone, idx) {
                if (zone.vertices.length <= 3) return;
                this.pushUndo();
                zone.vertices.splice(idx, 1);
                this.persistZoneVertices(zone.id, zone.vertices);
            },

            removeBarVertex(element, idx) {
                if (element.vertices.length <= 3) return;
                this.pushUndo();
                element.vertices.splice(idx, 1);
                this.persistBarVertices(element.id, element.vertices);
            },

            startZoneResize(event, zone) {
                if (!this.editMode) return;
                this.pushUndo();
                const startMX = event.clientX;
                const startMY = event.clientY;
                const startW  = zone.width;
                const startH  = zone.height;
                this.closeEditPanels();
                this.resizingId            = zone.id;
                document.body.style.cursor = 'se-resize';
                const onMove = (e) => {
                    const maxW   = Math.max(80, this.floorWidth  - zone.position_x);
                    const maxH   = Math.max(60, this.floorHeight - zone.position_y);
                    const newW   = Math.min(maxW, Math.max(80, startW + (e.clientX - startMX) / this.canvasZoom));
                    const newH   = Math.min(maxH, Math.max(60, startH + (e.clientY - startMY) / this.canvasZoom));
                    const testWH = { ...zone, width: Math.round(newW), height: Math.round(newH) };
                    const testW  = { ...zone, width: Math.round(newW) };
                    const testH  = { ...zone, height: Math.round(newH) };
                    if (!this.hasZoneCollision(testWH)) {
                        zone.width  = Math.round(newW);
                        zone.height = Math.round(newH);
                    } else if (!this.hasZoneCollision(testW)) {
                        zone.width  = Math.round(newW);
                    } else if (!this.hasZoneCollision(testH)) {
                        zone.height = Math.round(newH);
                    }
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    this.resizingId            = null;
                    document.body.style.cursor = '';
                    try {
                        await fetch(`/zonas/${zone.id}`, {
                            method:  'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ position_x: zone.position_x, position_y: zone.position_y, width: zone.width, height: zone.height }),
                        });
                    } catch { this.showToast('Error al guardar dimensiones.', true); }
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            async deleteZone(zone) {
                const confirmed = await Alpine.store('deleteModal').prompt(zone);
                if (!confirmed) return;
                try {
                    const res = await fetch(`/zonas/${zone.id}`, {
                        method:  'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar.', true); return; }
                    this.zones = this.zones.filter(z => z.id !== zone.id);
                    this.showToast(json.message);
                } catch { this.showToast('Error de red.', true); }
            },

            async deleteElement(element) {
                const confirmed = await Alpine.store('deleteModal').prompt(element);
                if (!confirmed) return;
                try {
                    const res = await fetch(`/mesas/${element.id}`, {
                        method:  'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar.', true); return; }
                    this.elements = this.elements.filter(e => e.id !== element.id);
                    this.showToast(json.message);
                } catch { this.showToast('Error de red.', true); }
            },

            startResize(event, table) {
                if (this.floorsEnabled && this.currentView === 'general') return;
                if (!this.editMode) return;
                this.pushUndo();
                const θRad    = (table.rotation ?? 0) * Math.PI / 180;
                const cosθ    = Math.cos(θRad);
                const sinθ    = Math.sin(θRad);
                const startMX = event.clientX;
                const startMY = event.clientY;
                const startW  = table.width;
                const startH  = table.height;
                const startPx = table.position_x;
                const startPy = table.position_y;
                this.closeEditPanels();
                this.resizingId            = table.id;
                document.body.style.cursor = 'se-resize';
                const onMove = (e) => {
                    const dx      = (e.clientX - startMX) / this.canvasZoom;
                    const dy      = (e.clientY - startMY) / this.canvasZoom;
                    const localDX =  dx * cosθ + dy * sinθ;
                    const localDY = -dx * sinθ + dy * cosθ;
                    const newW = Math.min(800, Math.max(20, startW + localDX));
                    const newH = Math.min(800, Math.max(20, startH + localDY));
                    const dW   = newW - startW;
                    const dH   = newH - startH;
                    const rawX = Math.round(startPx + dW / 2 * (cosθ - 1) - dH / 2 * sinθ);
                    const rawY = Math.round(startPy + dW / 2 * sinθ + dH / 2 * (cosθ - 1));
                    table.width      = Math.round(newW);
                    table.height     = Math.round(newH);
                    table.position_x = Math.max(0, Math.min(this.floorWidth  - Math.round(newW), rawX));
                    table.position_y = Math.max(0, Math.min(this.floorHeight - Math.round(newH), rawY));
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    this.resizingId            = null;
                    document.body.style.cursor = '';
                    if (this.hasCollision(table)) {
                        table.width      = startW;
                        table.height     = startH;
                        table.position_x = startPx;
                        table.position_y = startPy;
                        this.undoStack.pop();
                        this.showToast('No se puede superponer con otra mesa.', true);
                        await this.persistPosition(table.id, startPx, startPy, startW, startH);
                    } else {
                        await this.persistPosition(table.id, table.position_x, table.position_y, table.width, table.height);
                    }
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            async persistPosition(id, x, y, w, h) {
                const item = this.tables.find(t => t.id === id) ?? this.elements.find(e => e.id === id);
                if (item) { item.position_x = x; item.position_y = y; item.width = w; item.height = h; }
                try {
                    const res = await fetch(`/mesas/${id}/posicion`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ position_x: x, position_y: y, width: w, height: h, rotation: item?.rotation ?? 0 }),
                    });
                    if (!res.ok) this.showToast('Error al guardar la posición.', true);
                } catch { this.showToast('Error de red al guardar posición.', true); }
            },

            async updateShape(table, shape) {
                const prev = table.shape;
                table.shape = shape;
                this.editingTableId = null;
                this.editingTable   = null;
                this._editBtnEl     = null;
                try {
                    const res = await fetch(`/mesas/${table.id}/forma`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ shape }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { table.shape = prev; this.showToast(json.message ?? 'Error al cambiar la forma.', true); return; }
                    this.showToast(json.message);
                } catch { table.shape = prev; this.showToast('Error de red al cambiar la forma.', true); }
            },

            zoneFor(table) {
                return table.zone_id ? (this.zones.find(z => z.id == table.zone_id) ?? null) : null;
            },

            async updateZoneAssignment(table, zoneId) {
                const prev    = table.zone_id;
                table.zone_id = zoneId ? parseInt(zoneId) : null;
                try {
                    const res = await fetch(`/mesas/${table.id}/zona`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ zone_id: table.zone_id }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { table.zone_id = prev; this.showToast(json.message ?? 'Error al asignar la zona.', true); return; }
                    this.showToast(json.message);
                } catch { table.zone_id = prev; this.showToast('Error de red al asignar la zona.', true); }
            },

            async updateName(table, name) {
                name = name.trim();
                if (!name || name === table.name) return;
                const prev = table.name;
                table.name = name;
                try {
                    const res = await fetch(`/mesas/${table.id}/nombre`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ name }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { table.name = prev; this.showToast(json.message ?? 'Error al renombrar la mesa.', true); return; }
                    this.showToast(json.message);
                } catch { table.name = prev; this.showToast('Error de red al renombrar la mesa.', true); }
            },

            startRotation(event, table) {
                if (this.floorsEnabled && this.currentView === 'general') return;
                if (!this.editMode) return;
                this.pushUndo();
                this.closeEditPanels();
                this.rotatingId     = table.id;
                const ROTATE_CURSOR = "url('data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2720%27 height=%2720%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23ffffff%27 stroke-width=%275%27/%3E%3Cpath d=%27M21 2v6h-6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 12a9 9 0 0 1 15-6.7L21 8%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M3 22v-6h6%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3Cpath d=%27M21 12a9 9 0 0 1-15 6.7L3 16%27 stroke=%27%23111827%27 stroke-width=%272.5%27/%3E%3C/svg%3E') 10 10, grabbing";
                const canvasRect = this.$refs.canvas.getBoundingClientRect();
                const centerX    = canvasRect.left + (table.position_x + table.width  / 2) * this.canvasZoom;
                const centerY    = canvasRect.top  + (table.position_y + table.height / 2) * this.canvasZoom;
                this.isRotating            = true;
                this.rotTooltip.show       = true;
                document.body.style.cursor = ROTATE_CURSOR;
                let lastValidRotation = table.rotation ?? 0;
                const onMove = (e) => {
                    const dx       = e.clientX - centerX;
                    const dy       = e.clientY - centerY;
                    let   angle    = Math.atan2(dy, dx) * (180 / Math.PI) + 90;
                    angle = ((angle % 360) + 360) % 360;
                    table.rotation    = Math.round(angle);
                    lastValidRotation = table.rotation;
                    this.rotTooltip.x   = e.clientX;
                    this.rotTooltip.y   = e.clientY;
                    this.rotTooltip.deg = table.rotation;
                };
                const onUp = async () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                    this.isRotating            = false;
                    this.rotatingId            = null;
                    this.rotTooltip.show       = false;
                    document.body.style.cursor = '';
                    await this.persistPosition(table.id, table.position_x, table.position_y, table.width, table.height);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            startPan(event) {
                if (!this.editMode || this.currentView === 'general') return;
                if (event.button !== 0) return;
                const main = this.$refs.canvas?.parentElement;
                if (!main) return;
                this.isPanning = true;
                const startX   = event.clientX;
                const startY   = event.clientY;
                const scrollX  = main.scrollLeft;
                const scrollY  = main.scrollTop;
                document.body.style.cursor = 'grabbing';
                const onMove = (e) => {
                    if (!this.isPanning) return;
                    main.scrollLeft = scrollX - (e.clientX - startX);
                    main.scrollTop  = scrollY - (e.clientY - startY);
                };
                const onUp = () => {
                    this.isPanning             = false;
                    document.body.style.cursor = '';
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup',   onUp);
                };
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup',   onUp);
            },

            closeEditPanels() {
                this.editingTableId = null;
                this.editingTable   = null;
                this._editBtnEl     = null;
                this.editingZoneId  = null;
                this.editingZone    = null;
                this._zoneBtnEl     = null;
            },

            openContextMenu(event, item, type) {
                event.preventDefault();
                this.selectedId = item.id;
                this.closeEditPanels();
                const menuW = 220;
                const menuH = 340;
                const vw    = window.innerWidth;
                const vh    = window.innerHeight;
                let x, y;
                if (event.type !== 'contextmenu' || !event.clientX) {
                    const canvas = this.$refs.canvas;
                    const cRect  = canvas.getBoundingClientRect();
                    x = cRect.left + (item.position_x + item.width) * this.canvasZoom;
                    y = cRect.top  + (item.position_y + item.height / 2) * this.canvasZoom;
                } else {
                    x = event.clientX;
                    y = event.clientY;
                }
                if (x + menuW > vw) x = Math.max(0, vw - menuW - 8);
                if (y + menuH > vh) y = Math.max(0, vh - menuH - 8);
                this.contextMenu = { show: true, x, y, type, item };
            },

            closeContextMenu() {
                if (!this.contextMenu.show) return;
                this.contextMenu = { show: false, x: 0, y: 0, type: null, item: null };
            },

            panelPosFromItem(item, panelW) {
                return {
                    x: Math.max(4, item.position_x + item.width + 8),
                    y: Math.max(4, item.position_y),
                };
            },

            focusNextContextItem(current) {
                const items = [...document.querySelectorAll('#context-menu [role=menuitem]')]
                    .filter(el => el.offsetParent !== null);
                const idx = items.indexOf(current);
                items[(idx + 1) % items.length]?.focus();
            },

            focusPrevContextItem(current) {
                const items = [...document.querySelectorAll('#context-menu [role=menuitem]')]
                    .filter(el => el.offsetParent !== null);
                const idx = items.indexOf(current);
                items[(idx - 1 + items.length) % items.length]?.focus();
            },

            exitEditMode() {
                this.editMode  = false;
                this.closeEditPanels();
                this.selectedId = null;
                this.hoveredId  = null;
                Alpine.store('qrModal').close();
                Alpine.store('viewPanel').close();
                Alpine.store('tableModal').cancel();
                Alpine.store('deleteModal').resolve(false);
                Alpine.store('sizeModal').resolve(false);
                this._applyOverviewZoom();
            },

            isActive(id) {
                return this.selectedId     === id
                    || this.rotatingId     === id
                    || this.draggingId     === id
                    || this.resizingId     === id
                    || this.editingTableId === id
                    || this.editingZoneId  === id;
            },

            panelPosFromBtn(btn, panelW) {
                const canvas = this.$refs.canvas;
                const cRect  = canvas.getBoundingClientRect();
                const bRect  = btn.getBoundingClientRect();
                const zoom   = this.canvasZoom;
                const left = (bRect.left + bRect.width / 2 - cRect.left) / zoom - panelW / 2;
                const top  = (bRect.bottom - cRect.top) / zoom + 8;
                return { x: Math.max(4, left), y: Math.max(4, top) };
            },

            visibleTables() {
                if (!this.floorsEnabled || this.currentView === 'general') return this.tables;
                return this.tables.filter(t => (t.floor ?? 1) === this.currentFloor);
            },

            visibleElements() {
                if (!this.floorsEnabled || this.currentView === 'general') return this.elements;
                return this.elements.filter(e => (e.floor ?? 1) === this.currentFloor);
            },

            visibleZones() {
                if (!this.floorsEnabled || this.currentView === 'general') return this.zones;
                return this.zones.filter(z => (z.floor ?? 1) === this.currentFloor);
            },

            switchFloor(n) {
                this.currentFloor   = n;
                this.currentView    = 'floor';
                const size = this.floorCanvasSizes[n];
                if (size) { this.floorWidth  = size.width; this.floorHeight = size.height; }
                if (this.editMode) { this.canvasZoom = 1; } else { this._applyOverviewZoom(); }
                this.editingTableId = null;
                this.editingTable   = null;
                this._editBtnEl     = null;
                this.editingZoneId  = null;
                this.editingZone    = null;
                this._zoneBtnEl     = null;
            },

            switchView(view) {
                this.currentView = view;
                if (view === 'general') {
                    if (this.floorsEnabled) {
                        const sizes = Object.values(this.floorCanvasSizes);
                        if (sizes.length) {
                            this.floorWidth  = Math.max(...sizes.map(s => s.width));
                            this.floorHeight = Math.max(...sizes.map(s => s.height));
                        }
                    }
                    this.$nextTick(() => this._applyOverviewZoom());
                }
                this.closeEditPanels();
                this.selectedId = null;
                this.hoveredId  = null;
                Alpine.store('qrModal').close();
                Alpine.store('tableModal').cancel();
                Alpine.store('deleteModal').resolve(false);
                Alpine.store('sizeModal').resolve(false);
            },

            _applyOverviewZoom() {
                const headerH  = document.querySelector('header')?.offsetHeight ?? 65;
                const floorNavH = document.querySelector('nav[aria-label="Selector de planta"]')?.offsetHeight ?? 0;
                const availW   = window.innerWidth  - 32;
                const availH   = window.innerHeight - headerH - floorNavH - 32;
                const scaleX   = availW  / this.floorWidth;
                const scaleY   = availH  / this.floorHeight;
                this.canvasZoom = Math.min(1, Math.min(scaleX, scaleY));
                const main = this.$refs.canvas?.parentElement;
                if (main) { main.scrollTop = 0; main.scrollLeft = 0; }
            },

            async moveZoneToFloor(zone, newFloor) {
                if (!zone || newFloor === (zone.floor ?? 1)) return;
                const prev = zone.floor ?? 1;
                zone.floor = newFloor;
                try {
                    const res = await fetch(`/zonas/${zone.id}`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ floor: newFloor }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        zone.floor = prev;
                        this.showToast(json.message ?? 'Error al mover la zona.', true);
                        return;
                    }
                    this.editingZoneId = null;
                    this.editingZone   = null;
                    this._zoneBtnEl    = null;
                    this.showToast(json.message);
                } catch { zone.floor = prev; this.showToast('Error de red al mover la zona.', true); }
            },

            async toggleFloorsEnabled(enabled) {
                if (!enabled) {
                    const hasUpperStructures =
                        this.tables.some(t => (t.floor ?? 1) > 1) ||
                        this.elements.some(e => (e.floor ?? 1) > 1) ||
                        this.zones.some(z => (z.floor ?? 1) > 1);
                    if (hasUpperStructures) {
                        this.showToast('Elimina todas las estructuras de las plantas superiores antes de desactivar el sistema.', true);
                        return;
                    }
                    this.currentView  = 'floor';
                    this.currentFloor = 1;
                }
                const prev = this.floorsEnabled;
                this.floorsEnabled = enabled;
                try {
                    const res = await fetch(this._urls.floorSettings, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ floors_enabled: enabled }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        this.floorsEnabled = prev;
                        this.showToast(json.message ?? 'Error al guardar la configuración de plantas.', true);
                    }
                } catch { this.floorsEnabled = prev; this.showToast('Error al guardar la configuración de plantas.', true); }
            },

            async addFloor() {
                if (this.floorCount >= 5) return;
                const newCount = this.floorCount + 1;
                try {
                    const res = await fetch(this._urls.floorSettings, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ floor_count: newCount }),
                    });
                    const json = await res.json();
                    if (res.ok && json.success) {
                        this.floorCount = newCount;
                        const floor1Size = this.floorCanvasSizes[1] ?? { width: this.floorWidth, height: this.floorHeight };
                        this.floorCanvasSizes[newCount] = { ...floor1Size };
                        this.switchFloor(newCount);
                        this.showToast(`Planta ${newCount} creada.`);
                    }
                } catch { this.showToast('Error al crear la planta.', true); }
            },

            async confirmDeleteFloor(floor) {
                const structuresOnFloor = [
                    ...this.tables.filter(t => (t.floor ?? 1) === floor),
                    ...this.elements.filter(e => (e.floor ?? 1) === floor),
                ];
                const confirmed = await Alpine.store('deleteModal').prompt({
                    name: String(floor), shape: null, _isFloor: true, _count: structuresOnFloor.length,
                });
                if (!confirmed) return;
                try {
                    const res = await fetch(`/mesas/mapa/plantas/${floor}`, {
                        method:  'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar la planta.', true); return; }
                    this.tables   = this.tables.filter(t => (t.floor ?? 1) !== floor);
                    this.elements = this.elements.filter(e => (e.floor ?? 1) !== floor);
                    this.zones    = this.zones.filter(z => (z.floor ?? 1) !== floor);
                    this.floorCount = floor - 1;
                    if (this.currentFloor >= floor) this.switchFloor(floor - 1);
                    this.$nextTick(() => {
                        this.initTableInteract();
                        if (typeof this.initCanvasDropzone === 'function') this.initCanvasDropzone();
                    });
                    this.showToast(`Planta ${floor} eliminada.`);
                } catch { this.showToast('Error de red al eliminar la planta.', true); }
            },

            async moveToFloor(item, newFloor) {
                if (!item || newFloor === (item.floor ?? 1)) return;
                const prev = item.floor ?? 1;
                item.floor = newFloor;
                try {
                    const res = await fetch(`/mesas/${item.id}/planta`, {
                        method:  'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: JSON.stringify({ floor: newFloor }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { item.floor = prev; this.showToast(json.message ?? 'Error al mover la estructura.', true); return; }
                    this.editingTableId = null;
                    this.editingTable   = null;
                    this._editBtnEl     = null;
                    this.$nextTick(() => this.initTableInteract());
                    this.showToast(json.message);
                } catch { item.floor = prev; this.showToast('Error de red al mover la estructura.', true); }
            },

            async deleteTable(table) {
                const confirmed = await Alpine.store('deleteModal').prompt(table);
                if (!confirmed) return;
                try {
                    const res = await fetch(`/mesas/${table.id}`, {
                        method:  'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) { this.showToast(json.message ?? 'Error al eliminar la mesa.', true); return; }
                    this.tables = this.tables.filter(t => t.id !== table.id);
                    this.showToast(json.message);
                } catch { this.showToast('Error de red al eliminar la mesa.', true); }
            },

            _isTyping() {
                const tag = document.activeElement?.tagName;
                return tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA'
                    || document.activeElement?.isContentEditable;
            },

            getSelectedItem() {
                if (!this.selectedId) return null;
                return this.tables.find(t => t.id === this.selectedId)
                    ?? this.elements.find(e => e.id === this.selectedId)
                    ?? this.zones.find(z => z.id === this.selectedId)
                    ?? null;
            },

            async kbMove(dx, dy) {
                const item = this.getSelectedItem();
                if (!item) return;
                this.pushUndo();
                const { width: cw, height: ch } = this.sizeForItem(item);
                item.position_x = Math.max(0, Math.min(item.position_x + dx, cw - item.width));
                item.position_y = Math.max(0, Math.min(item.position_y + dy, ch - item.height));
                if (this.zones.some(z => z.id === item.id)) {
                    await this.persistZonePosition(item.id, item.position_x, item.position_y);
                } else {
                    await this.persistPosition(item.id, item.position_x, item.position_y, item.width, item.height);
                }
            },

            async kbRotate(delta) {
                const item = this.getSelectedItem();
                if (!item) return;
                this.pushUndo();
                item.rotation = ((item.rotation ?? 0) + delta + 360) % 360;
                if (this.zones.some(z => z.id === item.id)) {
                    await this.persistZoneRotation(item.id, item.rotation);
                } else {
                    await this.persistPosition(item.id, item.position_x, item.position_y, item.width, item.height);
                }
            },

            async kbResize(dw, dh) {
                const item = this.getSelectedItem();
                if (!item) return;
                this.pushUndo();
                const { width: cw, height: ch } = this.sizeForItem(item);
                const oldW = item.width;
                const oldH = item.height;
                item.width  = Math.max(40, Math.min(item.width  + dw, cw - item.position_x));
                item.height = Math.max(40, Math.min(item.height + dh, ch - item.position_y));
                if (item.vertices && item.vertices.length >= 3 && oldW > 0 && oldH > 0) {
                    const scaleX = item.width  / oldW;
                    const scaleY = item.height / oldH;
                    item.vertices.forEach(v => { v.x = Math.round(v.x * scaleX); v.y = Math.round(v.y * scaleY); });
                }
                if (this.zones.some(z => z.id === item.id)) {
                    try {
                        await fetch(`/zonas/${item.id}`, {
                            method:  'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ position_x: item.position_x, position_y: item.position_y, width: item.width, height: item.height }),
                        });
                    } catch { this.showToast('Error al guardar dimensiones.', true); }
                    if (item.vertices && item.vertices.length >= 3) await this.persistZoneVertices(item.id, item.vertices);
                } else {
                    await this.persistPosition(item.id, item.position_x, item.position_y, item.width, item.height);
                    if (item.vertices && item.vertices.length >= 3) await this.persistBarVertices(item.id, item.vertices);
                }
            },

            async kbDelete() {
                const item = this.getSelectedItem();
                if (!item) return;
                const isTable = this.tables.some(t => t.id === item.id);
                const isZone  = this.zones.some(z => z.id === item.id);
                if (isTable)     await this.deleteTable(item);
                else if (isZone) await this.deleteZone(item);
                else             await this.deleteElement(item);
                this.selectedId = null;
            },

            async kbMoveVertex(dx, dy) {
                if (this.focusedVertexIdx === null) return;
                const item = this.getSelectedItem();
                if (!item?.vertices) return;
                const v = item.vertices[this.focusedVertexIdx];
                if (!v) return;
                this.pushUndo();
                v.x = Math.max(0, Math.min(v.x + dx, item.width));
                v.y = Math.max(0, Math.min(v.y + dy, item.height));
                if (this.zones.some(z => z.id === item.id)) {
                    await this.persistZoneVertices(item.id, item.vertices);
                } else {
                    await this.persistBarVertices(item.id, item.vertices);
                }
            },

            copySelected() {
                if (this.readonly || !this.selectedId) return;
                const item = this.tables.find(t => t.id === this.selectedId)
                          ?? this.elements.find(e => e.id === this.selectedId);
                if (!item) return;
                const specialShapes = ['bar', 'stool', 'chair', 'fireplace', 'pillar', 'column'];
                this.clipboard = {
                    shape: item.shape,
                    width: item.width,
                    height: item.height,
                    rotation: item.rotation ?? 0,
                    is_service_point: !specialShapes.includes(item.shape),
                };
                this.showToast('Copiado. Ctrl+V para pegar.');
            },

            async pasteSelected() {
                if (!this.clipboard || this.readonly || !this.editMode) return;
                const src  = this.tables.find(t => t.id === this.selectedId) ?? this.elements.find(e => e.id === this.selectedId);
                const offX = src ? Math.min(src.position_x + 30, this.floorWidth  - this.clipboard.width)  : 100;
                const offY = src ? Math.min(src.position_y + 30, this.floorHeight - this.clipboard.height) : 100;
                if (this.clipboard.is_service_point) {
                    if (this.tables.length >= this._init.maxTables) {
                        this.showToast(`Límite de ${this._init.maxTables} mesas alcanzado.`, true);
                        return;
                    }
                    await this.createTable(null, this.clipboard.shape, offX, offY, this.clipboard.width, this.clipboard.height, this.clipboard.rotation);
                } else {
                    const pasteNames = { bar: 'Barra', chair: 'Silla', stool: 'Taburete', fireplace: 'Chimenea', pillar: 'Pilar', column: 'Columna' };
                    const name = pasteNames[this.clipboard.shape] ?? 'Elemento';
                    await this.createSpecialElement(name, this.clipboard.shape, offX, offY, this.clipboard.width, this.clipboard.height, this.clipboard.rotation);
                }
            },

            handleKb(event) {
                if (this.contextMenu.show) return;
                if (this._isTyping()) return;
                if (event.key === '?') {
                    event.preventDefault();
                    this.$store.helpModal.show = !this.$store.helpModal.show;
                    return;
                }
                const isArrow = event.key === 'ArrowUp' || event.key === 'ArrowDown'
                             || event.key === 'ArrowLeft' || event.key === 'ArrowRight';
                if (isArrow && this.selectedId) event.preventDefault();
                if (this.readonly || !this.editMode || !this.selectedId) return;
                const step  = event.shiftKey ? 1 : 10;
                const rStep = event.shiftKey ? 1 : 5;
                if (this.focusedVertexIdx !== null) {
                    const vStep = event.shiftKey ? 1 : 5;
                    switch (event.key) {
                        case 'ArrowUp':    this.kbMoveVertex(0, -vStep); break;
                        case 'ArrowDown':  this.kbMoveVertex(0,  vStep); break;
                        case 'ArrowLeft':  this.kbMoveVertex(-vStep, 0); break;
                        case 'ArrowRight': this.kbMoveVertex( vStep, 0); break;
                        case 'Escape':     this.focusedVertexIdx = null; break;
                    }
                    return;
                }
                if (event.altKey) {
                    switch (event.key) {
                        case 'ArrowRight': this.kbResize( step,    0); break;
                        case 'ArrowLeft':  this.kbResize(-step,    0); break;
                        case 'ArrowDown':  this.kbResize(0,     step); break;
                        case 'ArrowUp':    this.kbResize(0,    -step); break;
                    }
                    return;
                }
                switch (event.key) {
                    case 'ArrowUp':    this.kbMove(0, -step);                          break;
                    case 'ArrowDown':  this.kbMove(0,  step);                          break;
                    case 'ArrowLeft':  this.kbMove(-step, 0);                          break;
                    case 'ArrowRight': this.kbMove( step, 0);                          break;
                    case 'r': event.preventDefault(); this.kbRotate(event.shiftKey ? -1 : -5); break;
                    case 'e': event.preventDefault(); this.kbRotate(event.shiftKey ?  1 :  5); break;
                    case 'Delete':
                    case 'Backspace':  event.preventDefault(); this.kbDelete();         break;
                    case 'Escape':
                        this.selectedId       = null;
                        this.focusedVertexIdx = null;
                        this.closeEditPanels();
                        break;
                }
            },
        };
    });
}
