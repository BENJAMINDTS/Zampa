<?php
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\BarPanelController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Carta digital pública — accesible sin autenticación mediante QR
Route::get('/carta/{hash}', [MenuController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('menu.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
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

    // Gestión de mesas y códigos QR
    Route::get('/mesas', [TableController::class, 'index'])->name('tables.index');
    Route::get('/mesas/{table}/qr/descargar', [TableController::class, 'downloadQr'])->name('tables.qr.download');
    Route::post('/mesas/{table}/qr/regenerar', [TableController::class, 'regenerateHash'])->name('tables.qr.regenerate');

    // Panel de cocina — accesible para roles admin y kitchen
    Route::middleware('role:admin,kitchen')->group(function () {
        Route::get('/cocina', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::get('/cocina/badge', [KitchenController::class, 'badgeCount'])->name('kitchen.badge');
        Route::get('/cocina/pendientes', [KitchenController::class, 'pendingOrders'])->name('kitchen.pending');
        Route::post('/cocina/items/{item}/listo', [KitchenController::class, 'markItemReady'])->name('kitchen.item.ready');
        Route::post('/cocina/orders/{order}/servido', [KitchenController::class, 'markOrderServed'])->name('kitchen.order.served');
    });

    // Panel de barra — accesible para roles admin y waiter
    Route::middleware('role:admin,waiter')->group(function () {
        Route::get('/bar', [BarPanelController::class, 'index'])->name('bar.index');
        Route::get('/bar/badge', [BarPanelController::class, 'badgeCount'])->name('bar.badge');
        Route::get('/bar/pendientes', [BarPanelController::class, 'pendingItems'])->name('bar.pending');
        Route::patch('/bar/items/{item}', [BarPanelController::class, 'updateItem'])->name('bar.items.update');
    });

    // Notificaciones al camarero cuando una comanda está lista
    Route::get('/notifications/ready', [NotificationController::class, 'ready'])
         ->name('notifications.ready');
    Route::patch('/notifications/{order}/dismiss', [NotificationController::class, 'dismiss'])
         ->name('notifications.dismiss');
});

require __DIR__.'/auth.php';
