<?php

use App\Http\Controllers\Api\BillRequestController;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\MixedPaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\SplitPaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DailyMenuPublicController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/orders', [OrderController::class, 'store'])->name('api.orders.store');

Route::post('/v1/bill-request/{hash}', [BillRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.bill.request');

// Pago con tarjeta — rutas públicas (sin autenticación, contexto por table_hash)
Route::get('/v1/payment/{hash}/total',    [CardPaymentController::class, 'total'])
    ->middleware('throttle:30,1')
    ->name('api.payment.total');
Route::post('/v1/payment/{hash}/intent',  [CardPaymentController::class, 'intent'])
    ->middleware('throttle:10,1')
    ->name('api.payment.intent');
Route::post('/v1/payment/{hash}/confirm', [CardPaymentController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('api.payment.confirm');

// Cobro partido — rutas públicas (sin autenticación, contexto por table_hash)
Route::get('/v1/payment/{hash}/split/items',       [SplitPaymentController::class, 'claimedItems'])
    ->middleware('throttle:30,1')
    ->name('api.split.items');
Route::post('/v1/payment/{hash}/split/pay-items',  [SplitPaymentController::class, 'payItems'])
    ->middleware('throttle:10,1')
    ->name('api.split.pay-items');
Route::post('/v1/payment/{hash}/split/pay-eq',     [SplitPaymentController::class, 'payEquitative'])
    ->middleware('throttle:10,1')
    ->name('api.split.pay-eq');
Route::post('/v1/payment/{hash}/split/confirm',    [SplitPaymentController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('api.split.confirm');

// Cobro mixto — rutas públicas (sin autenticación, contexto por table_hash)
Route::post('/v1/payment/{hash}/mixed/intent',  [MixedPaymentController::class, 'intent'])
    ->middleware('throttle:10,1')
    ->name('api.mixed.intent');
Route::post('/v1/payment/{hash}/mixed/confirm', [MixedPaymentController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('api.mixed.confirm');

// Chatbot IA — rutas públicas (sin autenticación, contexto por table_hash)
Route::get('/v1/menu/{tableHash}',             [ChatController::class, 'menu'])->middleware('throttle:60,1')->name('api.chat.menu');
Route::post('/v1/chat/{tableHash}/start',      [ChatController::class, 'start'])->name('api.chat.start');
Route::post('/v1/chat/{conversation}/message', [ChatController::class, 'send'])->middleware('throttle:60,1')->name('api.chat.send');
Route::delete('/v1/chat/{conversation}',       [ChatController::class, 'close'])->name('api.chat.close');

// Menú del día — rutas públicas (sin autenticación, contexto por table_hash)
Route::get('/v1/menu/{hash}/daily-menu',        [DailyMenuPublicController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('api.daily-menu.show');
Route::post('/v1/menu/{hash}/daily-menu/order', [DailyMenuPublicController::class, 'order'])
    ->middleware('throttle:10,1')
    ->name('api.daily-menu.order');
