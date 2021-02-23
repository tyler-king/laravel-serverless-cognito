<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', 'LoginController@login');
Route::get('/profile', 'LoginController@showProfile');
Route::get('/logout', 'LoginController@logout');
