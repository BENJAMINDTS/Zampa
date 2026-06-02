<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Panel centralizado del camarero.
 *
 * Expone el mapa de mesas con estado en tiempo real, panel lateral de detalle
 * por mesa, marcado de ítems como servidos y gestión de notificaciones.
 *
 * @author SebastianBCF
 * @author BenjaminDTS
 */
class BarPanelController extends Controller
{
    /**
     * Vista principal del camarero: mapa de mesas con estado en tiempo real.
     *
     * @return View
     */
    public function index(): View
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId     = Auth::user()->ownerUserId();
        $owner       = Auth::user()->isAdmin() ? Auth::user() : Auth::user()->admin;
        $tables      = Table::where('user_id', $ownerId)
                            ->servicePoints()
                            ->with(['activeOrder'])
                            ->orderBy('name')
                            ->get();
        $zones       = Zone::where('user_id', $ownerId)->orderBy('name')->get();
        $floorWidth  = $owner?->floor_width  ?? 1200;
        $floorHeight = $owner?->floor_height ?? 800;
        $readyOrders = Order::with('table')
                            ->where('notification_ready', true)
                            ->whereHas('table', fn ($q) => $q->where('user_id', $ownerId))
                            ->get()
                            ->map(fn (Order $o) => ['id' => $o->id, 'table' => $o->table->name])
                            ->values();
        $orders      = Order::with([
                                'table',
                                'items' => fn ($q) => $q->where('destination', 'bar')
                                    ->where('status', 'queued')
                                    ->with('product'),
                            ])
                            ->whereIn('status', ['pending', 'cooking'])
                            ->whereHas('table', fn ($q) => $q->where('user_id', $ownerId))
                            ->whereHas('items', fn ($q) => $q->where('destination', 'bar')->where('status', 'queued'))
                            ->get();

        return view('bar.index', compact('tables', 'zones', 'floorWidth', 'floorHeight', 'readyOrders', 'orders'));
    }

    /**
     * Endpoint para el badge del nav: conteo de pedidos con ítems de barra pendientes.
     *
     * @return JsonResponse
     */
    public function badgeCount(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        $count = Order::whereHas('table', fn($q) => $q->where('user_id', $ownerId))
            ->where(fn($q) => $q
                ->where(fn($inner) => $inner
                    ->whereIn('status', ['pending', 'cooking'])
                    ->whereHas('items', fn($q) => $q->where('destination', 'bar')->where('status', 'queued'))
                )
                ->orWhere('notification_ready', true)
                ->orWhere('bill_requested', true)
            )
            ->count();

        return response()->json(['pending_count' => $count]);
    }

    /**
     * Endpoint de polling único: devuelve el estado de todas las mesas del restaurante
     * incluyendo pedido activo, ítems pendientes, solicitudes de cuenta y notificaciones.
     * Reemplaza los tres pollers independientes del panel anterior.
     *
     * @return JsonResponse
     */
    public function tableStatus(): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        $owner   = Auth::user()->isAdmin() ? Auth::user() : Auth::user()->admin;

        $tables = Table::where('user_id', $ownerId)
            ->servicePoints()
            ->with(['activeOrder.items'])
            ->get()
            ->map(fn(Table $t) => [
                'id'                       => $t->id,
                'name'                     => $t->name,
                'shape'                    => $t->shape,
                'x'                        => $t->position_x,
                'y'                        => $t->position_y,
                'width'                    => $t->width,
                'height'                   => $t->height,
                'rotation'                 => $t->rotation ?? 0,
                'zone_id'                  => $t->zone_id,
                'order_id'                 => $t->activeOrder?->id,
                'bill_requested'           => (bool) ($t->activeOrder?->bill_requested ?? false),
                'requested_payment_method' => $t->activeOrder?->requested_payment_method,
                'notification_ready'       => (bool) ($t->activeOrder?->notification_ready ?? false),
                'pending_bar_items'        => $t->activeOrder
                    ? $t->activeOrder->items->whereIn('status', ['queued', 'ready'])->where('destination', 'bar')->count()
                    : 0,
                'status'                   => $this->computeTableStatus($t),
            ])
            ->values();

        $zones = Zone::where('user_id', $ownerId)->get()->map(fn(Zone $z) => [
            'id'     => $z->id,
            'name'   => $z->name,
            'x'      => $z->position_x,
            'y'      => $z->position_y,
            'width'  => $z->width,
            'height' => $z->height,
            'color'  => $z->color ?? '#fef3c7',
        ])->values();

        return response()->json([
            'polled_at' => now()->toISOString(),
            'floor'     => [
                'width'  => $owner?->floor_width  ?? 1200,
                'height' => $owner?->floor_height ?? 800,
            ],
            'zones'     => $zones,
            'tables'    => $tables,
        ]);
    }

    /**
     * Devuelve el detalle completo de una mesa: pedido activo e ítems agrupados
     * en pendientes (queued/ready) y servidos. Usado por el panel lateral.
     *
     * @param  Table  $table
     * @return JsonResponse
     */
    public function tableDetail(Table $table): JsonResponse
    {
        abort_if($table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');

        $order = $table->activeOrder;

        if (! $order) {
            return response()->json([
                'table' => ['id' => $table->id, 'name' => $table->name, 'status' => 'free'],
                'order' => null,
                'items' => ['pending' => [], 'served' => []],
            ]);
        }

        $order->loadMissing('items.product');

        $mapItem = fn(OrderItem $i) => [
            'id'           => $i->id,
            'product_name' => $i->product->name,
            'quantity'     => $i->quantity,
            'status'       => $i->status,
            'destination'  => $i->destination,
            'price'        => $i->price,
        ];

        return response()->json([
            'table' => [
                'id'     => $table->id,
                'name'   => $table->name,
                'status' => $this->computeTableStatus($table),
            ],
            'order' => [
                'id'                       => $order->id,
                'created_at'               => $order->created_at->format('H:i'),
                'status'                   => $order->status,
                'total'                    => $order->total,
                'bill_requested'           => (bool) $order->bill_requested,
                'requested_payment_method' => $order->requested_payment_method,
                'notification_ready'       => (bool) $order->notification_ready,
            ],
            'items' => [
                'pending' => $order->items->whereIn('status', ['queued', 'ready'])->map($mapItem)->values(),
                'served'  => $order->items->where('status', 'served')->map($mapItem)->values(),
            ],
        ]);
    }

    /**
     * Marca un ítem como servido (entregado físicamente al cliente).
     * Acepta ítems de cualquier destino (bar o cocina) con status=ready.
     * Si todos los ítems quedan servidos, cierra el pedido como servido.
     *
     * @param  OrderItem  $item
     * @return JsonResponse
     */
    public function markItemServed(OrderItem $item): JsonResponse
    {
        $order = $item->order()->with('table')->first();

        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($item->status !== 'ready', 422, 'El ítem no está listo para entregar.');
        abort_if(in_array($order->status, ['closed', 'served']), 422, 'El pedido ya está cerrado.');

        $item->update(['status' => 'served']);

        $allServed = $order->fresh()->items()->whereNotIn('status', ['served'])->doesntExist();

        if ($allServed) {
            $order->update(['status' => 'served', 'notification_ready' => false]);
        }

        return response()->json([
            'item_id'      => $item->id,
            'order_id'     => $order->id,
            'all_served'   => $allServed,
            'order_status' => $order->fresh()->status,
        ]);
    }

    /**
     * Marca un ítem de barra como listo (preparado) y cierra el pedido
     * si todos sus ítems están preparados.
     *
     * @param  OrderItem  $item
     * @return JsonResponse
     */
    public function updateItem(OrderItem $item): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $order = $item->order()->with('table')->first();

        abort_if($order->table->user_id !== Auth::user()->ownerUserId(), 403, 'Acceso denegado.');
        abort_if($item->destination !== 'bar', 403, 'Este ítem no pertenece a la barra.');

        $item->update(['status' => 'ready']);

        if ($order->status === 'pending') {
            $order->update(['status' => 'cooking']);
        }

        $allReady = $order->fresh()->items()
            ->whereNotIn('status', ['ready', 'served'])
            ->doesntExist();

        if ($allReady) {
            $order->update(['status' => 'ready', 'notification_ready' => true]);
        }

        return response()->json([
            'item_id'      => $item->id,
            'order_id'     => $order->id,
            'order_status' => $order->fresh()->status,
            'all_ready'    => $allReady,
        ]);
    }

    /**
     * Vista de gestión de disponibilidad de productos de barra.
     *
     * @return View
     */
    public function barAvailability(): View
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId     = Auth::user()->ownerUserId();
        $barProducts = $this->getBarProducts($ownerId);

        return view('bar.availability', compact('barProducts'));
    }

    /**
     * Activa o desactiva temporalmente un producto de barra durante el servicio.
     * Solo permite gestionar productos cuya categoría tenga destination=bar.
     *
     * @param  Product  $product
     * @return JsonResponse
     */
    public function toggleAvailability(Product $product): JsonResponse
    {
        abort_if(! Auth::user()->canAccessBar(), 403, 'Acceso denegado.');

        $ownerId = Auth::user()->ownerUserId();
        abort_if($product->user_id !== $ownerId, 403, 'Acceso denegado.');

        $isBarProduct = $product->categories()
            ->where('destination', 'bar')
            ->exists();
        abort_if(! $isBarProduct, 403, 'Este producto no pertenece a la barra.');

        $product->update(['is_available' => ! $product->is_available]);

        Cache::forget("menu:{$ownerId}");

        return response()->json([
            'product_id'   => $product->id,
            'is_available' => $product->is_available,
        ]);
    }

    /**
     * @deprecated Usar tableStatus() en su lugar. Se mantiene por compatibilidad.
     * @return JsonResponse
     */
    public function pendingItems(): JsonResponse
    {
        return $this->tableStatus();
    }

    /**
     * Devuelve los productos activos de barra del restaurante para gestionar disponibilidad.
     *
     * @param  int  $ownerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getBarProducts(int $ownerId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('user_id', $ownerId)
            ->where('is_active', true)
            ->whereHas('categories', fn ($q) => $q->where('destination', 'bar'))
            ->with('categories:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'is_available']);
    }

    /**
     * Calcula el estado visual de una mesa en el mapa del camarero.
     * Prioridad: bill_requested > order_ready > occupied > free.
     *
     * @param  Table  $table
     * @return string
     */
    private function computeTableStatus(Table $table): string
    {
        $order = $table->activeOrder;
        if (! $order)                  return 'free';
        if ($order->bill_requested)    return 'bill_requested';
        if ($order->notification_ready) return 'order_ready';
        return 'occupied';
    }
}
