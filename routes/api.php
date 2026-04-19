<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/orders', [OrderController::class, 'store'])->name('api.orders.store');

// Chatbot IA — rutas públicas (sin autenticación, contexto por table_hash)
Route::post('/v1/chat/{tableHash}/start',      [ChatController::class, 'start'])->name('api.chat.start');
Route::post('/v1/chat/{conversation}/message', [ChatController::class, 'send'])->name('api.chat.send');
Route::delete('/v1/chat/{conversation}',       [ChatController::class, 'close'])->name('api.chat.close');
