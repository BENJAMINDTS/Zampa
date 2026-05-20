<?php
use App\Http\Controllers\DailyMenuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\BarPanelController;
use App\Http\Controllers\ManagerRevenueController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SplitPaymentConfigController;
use App\Http\Controllers\TapasController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ZoneController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminPlanController;
use App\Http\Controllers\SuperAdmin\SuperAdminBusinessController;
use App\Http\Controllers\SuperAdmin\SuperAdminMapController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Carta digital pública — accesible sin autenticación mediante QR
Route::get('/carta/{hash}', [MenuController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('menu.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'business.active', 'role:admin'])
    ->name('dashboard');

Route::middleware(['auth', 'business.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('categories', CategoryController::class);
    Route::resource('ingredients', IngredientController::class);
    Route::resource('products', ProductController::class);
    Route::get('/productos/{product}/ingredientes', [ProductController::class, 'editIngredients'])
         ->name('products.ingredients.edit');
    Route::post('/productos/{product}/ingredientes', [ProductController::class, 'syncIngredients'])
         ->name('products.ingredients.sync');

    // Rutas exclusivas del gerente (admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/tapas/config', [TapasController::class, 'edit'])->name('tapas.edit');
        Route::put('/tapas/config', [TapasController::class, 'update'])->name('tapas.update');

        Route::get('/split-payment/config', [SplitPaymentConfigController::class, 'edit'])->name('split-payment.edit');
        Route::put('/split-payment/config', [SplitPaymentConfigController::class, 'update'])->name('split-payment.update');

        Route::get('/manager/income', [ManagerRevenueController::class, 'index'])->name('manager.income');

        // Gestión de personal del restaurante
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

        // Mapa visual de mesas (Bloques 8.1 y 8.3) — solo admin puede editar
        Route::post('/mesas', [TableController::class, 'store'])->name('tables.store');
        Route::patch('/mesas/mapa/canvas', [TableController::class, 'updateCanvas'])->name('tables.canvas.update');
        Route::patch('/mesas/mapa/plantas/config', [TableController::class, 'updateFloorSettings'])->name('tables.floor-settings');
        Route::delete('/mesas/mapa/plantas/{floor}', [TableController::class, 'destroyFloor'])->name('tables.floor-destroy');
        Route::patch('/mesas/{table}/posicion', [TableController::class, 'updatePosition'])->name('tables.updatePosition');
        Route::patch('/mesas/{table}/forma', [TableController::class, 'updateShape'])->name('tables.updateShape');
        Route::patch('/mesas/{table}/nombre', [TableController::class, 'updateName'])->name('tables.updateName');
        Route::patch('/mesas/{table}/zona', [TableController::class, 'updateZone'])->name('tables.updateZone');
        Route::patch('/mesas/{table}/planta', [TableController::class, 'updateFloor'])->name('tables.update-floor');
        Route::patch('/mesas/{table}/vertices', [TableController::class, 'updateVertices'])->name('tables.updateVertices');
        Route::delete('/mesas/{table}', [TableController::class, 'destroy'])->name('tables.destroy');

        // Gestión de zonas del plano — solo admin
        Route::post('/zonas', [ZoneController::class, 'store'])->name('zones.store');
        Route::patch('/zonas/{zone}', [ZoneController::class, 'update'])->name('zones.update');
        Route::delete('/zonas/{zone}', [ZoneController::class, 'destroy'])->name('zones.destroy');

        // Menú del Día — gestión del gerente (Bloque 14.2)
        Route::resource('daily-menus', DailyMenuController::class)
             ->except(['show'])
             ->parameters(['daily-menus' => 'dailyMenu']);
        Route::get('/daily-menus/{dailyMenu}/sections', [DailyMenuController::class, 'sections'])
             ->name('daily-menus.sections');
        Route::post('/daily-menus/{dailyMenu}/sections', [DailyMenuController::class, 'storeSection'])
             ->name('daily-menus.sections.store');
        Route::patch('/daily-menus/{dailyMenu}/sections/{section}', [DailyMenuController::class, 'updateSection'])
             ->name('daily-menus.sections.update');
        Route::delete('/daily-menus/{dailyMenu}/sections/{section}', [DailyMenuController::class, 'destroySection'])
             ->name('daily-menus.sections.destroy');
        Route::post('/daily-menus/{dailyMenu}/sections/{section}/products/sync', [DailyMenuController::class, 'syncProducts'])
             ->name('daily-menus.sync-products');
        Route::post('/daily-menus/{dailyMenu}/timing', [DailyMenuController::class, 'storeTiming'])
             ->name('daily-menus.timing');
    });

    // Mapa visual — lectura para admin y camarero, edición solo admin
    Route::middleware('role:admin,waiter')->group(function () {
        Route::get('/mesas/mapa', [TableController::class, 'map'])->name('tables.map');
        Route::get('/mesas/mapa/estados', [TableController::class, 'mapStatuses'])->name('tables.map.statuses');
    });

    // Gestión de mesas y códigos QR
    Route::get('/mesas', [TableController::class, 'index'])->name('tables.index');
    Route::get('/mesas/{table}/qr', [TableController::class, 'showQr'])->name('tables.qr.show');
    Route::get('/mesas/{table}/qr/descargar', [TableController::class, 'downloadQr'])->name('tables.qr.download');
    Route::post('/mesas/{table}/qr/regenerar', [TableController::class, 'regenerateHash'])->name('tables.qr.regenerate');

    // Panel de cocina — requiere canAccessKitchen() (rol nativo 'kitchen' o admin con is_kitchen=true)
    Route::middleware('can.kitchen')->group(function () {
        Route::get('/cocina', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::get('/cocina/badge', [KitchenController::class, 'badgeCount'])->name('kitchen.badge');
        Route::get('/cocina/pendientes', [KitchenController::class, 'pendingOrders'])->name('kitchen.pending');
        Route::post('/cocina/items/{item}/listo', [KitchenController::class, 'markItemReady'])->name('kitchen.item.ready');
        Route::post('/cocina/orders/{order}/servido', [KitchenController::class, 'markOrderServed'])->name('kitchen.order.served');
    });

    // Panel de barra y notificaciones al camarero — requiere canAccessBar() (rol nativo 'waiter' o admin con is_waiter=true)
    Route::middleware('can.bar')->group(function () {
        Route::get('/bar', [BarPanelController::class, 'index'])->name('bar.index');
        Route::get('/bar/badge', [BarPanelController::class, 'badgeCount'])->name('bar.badge');
        Route::get('/bar/status', [BarPanelController::class, 'tableStatus'])->name('bar.table.status');
        Route::get('/bar/tables/{table}', [BarPanelController::class, 'tableDetail'])->name('bar.table.detail');
        Route::get('/bar/pendientes', [BarPanelController::class, 'pendingItems'])->name('bar.pending');
        Route::patch('/bar/items/{item}', [BarPanelController::class, 'updateItem'])->name('bar.items.update');
        Route::patch('/bar/items/{item}/served', [BarPanelController::class, 'markItemServed'])->name('bar.items.served');

        Route::get('/notifications/ready', [NotificationController::class, 'ready'])
             ->name('notifications.ready');
        Route::patch('/notifications/{order}/dismiss', [NotificationController::class, 'dismiss'])
             ->name('notifications.dismiss');

        Route::get('/notifications/bill-requests', [NotificationController::class, 'billRequests'])
             ->name('notifications.bill.requests');
        Route::patch('/notifications/{order}/dismiss-bill-request', [NotificationController::class, 'dismissBillRequest'])
             ->name('notifications.bill.dismiss');

        // Pagos desde la mesa — efectivo
        Route::post('/payments/{order}/cash', [PaymentController::class, 'cashPayment'])
             ->name('payments.cash');
    });
});

// Panel de superadministración — acceso exclusivo a rol superadmin
Route::middleware(['auth', 'role.superadmin'])
     ->prefix('superadmin')
     ->name('superadmin.')
     ->group(function () {

    Route::get('/', fn () => redirect()->route('superadmin.dashboard'));

    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
         ->name('dashboard');

    // Gestión de planes (Bloque 13.3)
    Route::resource('plans', SuperAdminPlanController::class);

    // Gestión de negocios (Bloque 13.4)
    Route::resource('businesses', SuperAdminBusinessController::class)
         ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('businesses/{business}/toggle', [SuperAdminBusinessController::class, 'toggle'])
         ->name('businesses.toggle');

    // Mapa de negocios (Bloque 13.5)
    Route::get('map', [SuperAdminMapController::class, 'index'])
         ->name('map');
});

require __DIR__.'/auth.php';
