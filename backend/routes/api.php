<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', [\App\Http\Controllers\HealthCheckController::class, 'check']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
