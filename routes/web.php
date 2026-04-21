<?php
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuController;
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
        Route::get('/cocina/pendientes', [KitchenController::class, 'pendingOrders'])->name('kitchen.pending');
        Route::post('/cocina/items/{item}/listo', [KitchenController::class, 'markItemReady'])->name('kitchen.item.ready');
    });
});

require __DIR__.'/auth.php';
