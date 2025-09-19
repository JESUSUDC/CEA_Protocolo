<?php

use Illuminate\Support\Facades\Route;
use Infrastructure\Entrypoint\Rest\Users\Controller\UserController;

/*
| API routes for Users (hexagonal entrypoint)
| We recommend proteger las rutas sensibles con middleware 'auth:api' o JWT middleware.
*/

Route::prefix('v1')->group(function () {
    // Public
    Route::post('/users', [UserController::class, 'store']); // register
    Route::post('/users/login', [UserController::class, 'login']); // login

    // Protected (ejemplo con middleware jwt.auth) — ajustar según tu stack
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/change-password', [UserController::class, 'changePassword']);
        Route::post('/users/{id}/logout', [UserController::class, 'logout']);
    });
});
