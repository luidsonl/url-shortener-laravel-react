<?php

use App\Http\Controllers\NewPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return '<h1>API is running</h1>';
});

Route::get('/reset-password', [NewPasswordController::class, 'resetPasswordForm'])
    ->name('password.reset');
