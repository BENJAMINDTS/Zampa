<?php

use App\Http\Controllers\Api\BillRequestController;
use App\Http\Controllers\Api\CardPaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/orders', [OrderController::class, 'store'])->name('api.orders.store');

Route::post('/v1/bill-request/{hash}', [BillRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('api.bill.request');

// Pago con tarjeta — rutas públicas (sin autenticación, contexto por table_hash)
Route::post('/v1/payment/{hash}/intent',  [CardPaymentController::class, 'intent'])
    ->middleware('throttle:10,1')
    ->name('api.payment.intent');
Route::post('/v1/payment/{hash}/confirm', [CardPaymentController::class, 'confirm'])
    ->middleware('throttle:10,1')
    ->name('api.payment.confirm');

// Chatbot IA — rutas públicas (sin autenticación, contexto por table_hash)
Route::post('/v1/chat/{tableHash}/start',      [ChatController::class, 'start'])->name('api.chat.start');
Route::post('/v1/chat/{conversation}/message', [ChatController::class, 'send'])->middleware('throttle:60,1')->name('api.chat.send');
Route::delete('/v1/chat/{conversation}',       [ChatController::class, 'close'])->name('api.chat.close');
