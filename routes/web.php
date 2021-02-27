<?php

use Illuminate\Support\Facades\Route;

use TKing\ServerlessCognito\Http\Controllers\LoginController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/login', [LoginController::class, 'login']);
    Route::get('/login/callback', [LoginController::class, 'hash'])->name("callback");
    Route::post('/login/callback', [LoginController::class, 'readHash']);
    Route::get('/user', [LoginController::class, 'user']);
    Route::get('/logout', [LoginController::class, 'logout']);
});
