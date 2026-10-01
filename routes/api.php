<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users/create', [UserController::class, 'store']);
Route::post('/users/login', [UserController::class, 'login']);
Route::put('/users/update-username', [UserController::class, 'updateUsername']);
Route::put('/users/update-email', [UserController::class, 'updateEmail']);
Route::put('/users/update-password', [UserController::class, 'updatePassword']);
Route::delete('/users/delete', [UserController::class, 'delete']);
