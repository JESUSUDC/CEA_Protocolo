<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\Entrypoint\Rest\Users\Controller\UserController;
use App\Infrastructure\Entrypoint\Rest\Cellphones\Controller\CellphoneController;
use App\Infrastructure\Entrypoint\Rest\Cellphones\Controller\CellController;

/*
| API routes for Users (hexagonal entrypoint)
| Se recomienda proteger las rutas sensibles con middleware 'auth:api' o JWT middleware.
*/

Route::prefix('v1')->group(function () {
    // -------------------------
    // Users
    // -------------------------
    // Public
    Route::post('/users', [UserController::class, 'store']); // register
    Route::post('/users/login', [UserController::class, 'login']); // login
    Route::post('/users/refresh', [UserController::class, 'refresh']); // token refresh

    // Protected
    //Route::middleware(['auth:api'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/change-password', [UserController::class, 'changePassword']);
        Route::post('/users/{id}/logout', [UserController::class, 'logout']);
    //});

    // -------------------------
    // Cellphones
    // -------------------------
    // Public (registro de celulares si aplica)
    Route::post('/cellphones', [CellphoneController::class, 'store']); // register cellphone

    // Protected
    //Route::middleware(['auth:api'])->group(function () {
        Route::get('/cellphones', [CellphoneController::class, 'index']); // list
        Route::get('/cellphones/{id}', [CellphoneController::class, 'show']); // get by id
        Route::put('/cellphones/{id}', [CellphoneController::class, 'update']); // update
        Route::delete('/cellphones/{id}', [CellphoneController::class, 'destroy']); // delete
    //});
});
