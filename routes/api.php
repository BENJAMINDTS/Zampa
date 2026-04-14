<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/orders', [OrderController::class, 'store'])->name('api.orders.store');
