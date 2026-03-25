<?php

use App\Http\Controllers\AppUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('users', AppUserController::class);
