<?php

namespace App\Http\Middleware;

use App\Http\Controllers\CardsAndCollectionsController;
use App\Http\Controllers\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Middleware que verifica a los admins
Route::middleware(['validateToken', 'validateRole'])->group(function () {

    Route::put('/register', [CardsAndCollectionsController::class, 'register']);
    Route::put('/registerCollection', [CardsAndCollectionsController::class, 'registerCollection']);

});

// Middleware de verificación para gestión de ventas (Profesionales y particulares)
Route::middleware(['validateToken', 'verifyToSell'])->group(function () {

    Route::put('/cardsToSale', [CardsAndCollectionsController::class, 'cardsToSale']);
    Route::get('/searchCard', [CardsAndCollectionsController::class, 'searchCard']);
    
});


// Gestión de usuarios
Route::prefix('/user')->group(function() {

    Route::put('/register', [UsersController::class, 'register']);
    Route::put('/login', [UsersController::class, 'login']);
    Route::put('/recoveryPassword', [UsersController::class, 'recoveryPassword']);

});


