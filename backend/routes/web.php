<?php

use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('swagger');
});

Route::get('/reset-password', [NewPasswordController::class, 'resetPasswordForm'])
    ->name('password.reset');

Route::get('/{code}', [ShortLinkController::class, 'redirect'])
    ->where('code', '[A-Za-z0-9]+')
    ->name('shortlink.redirect');