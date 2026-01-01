<?php

use App\Http\Controllers\NewPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/reset-password', [NewPasswordController::class, 'resetPasswordForm'])
    ->name('password.reset');
