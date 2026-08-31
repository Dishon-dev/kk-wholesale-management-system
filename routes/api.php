<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');

    /** Authentication Endpoints */
    Route::group(['prefix' => '/auth'], function () {
        //login, register, forgot password, logout
    });

    /** Shopfront Endpoints */
    Route::prefix('shopfront')->group( function () {

    });

    /** Protected Endpoints */
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    });
});
