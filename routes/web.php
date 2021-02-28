<?php

use Illuminate\Support\Facades\Route;

use TKing\ServerlessCognito\Http\Controllers\LoginController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/login', [LoginController::class, 'login'])->name("login");
    Route::get('/login/callback', [LoginController::class, 'hash'])->name("callback");
    Route::post('/login/callback', [LoginController::class, 'readHash']);
    Route::get('/logout', [LoginController::class, 'logout'])->name("logout");
});

Route::group(['middleware' => ['api.cognito']], function () {
    Route::get('/user', [LoginController::class, 'user'])->name("profile");
});
