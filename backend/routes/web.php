<?php

use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return '<h1>API is running</h1>';
});

Route::get('/reset-password', [NewPasswordController::class, 'resetPasswordForm'])
    ->name('password.reset');

Route::get('/{code}', [ShortLinkController::class, 'redirect'])
    ->where('code', '[A-Za-z0-9]+')
    ->name('shortlink.redirect');