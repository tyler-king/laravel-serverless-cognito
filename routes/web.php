<?php

use Illuminate\Support\Facades\Route;

Route::namespace('TKing\ServerlessCognito\Http\Controllers')->group(function () {
    Route::get('/login', 'LoginController@login');
    Route::get('/profile', 'LoginController@showProfile');
    Route::get('/logout', 'LoginController@logout');
});
