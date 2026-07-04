<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\FlightcaseController;
use App\Http\Controllers\Api\IncidentController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ManifestController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', function (Request $request) {
        return response()->json(['data' => $request->user()]);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Master Data
    Route::apiResource('events', EventController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('flightcases', FlightcaseController::class);

    // Manifests
    Route::get('/manifests', [ManifestController::class, 'index']);
    Route::get('/manifests/{id}', [ManifestController::class, 'show']);
    Route::post('/manifests/outbound', [ManifestController::class, 'storeOutbound']);
    Route::post('/manifests/inbound', [ManifestController::class, 'storeInbound']);
    Route::put('manifests/{id}/complete', [ManifestController::class, 'complete']);

    // Incidents
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::get('/incidents/{id}', [IncidentController::class, 'show']);
    Route::patch('/incidents/{id}/resolve', [IncidentController::class, 'resolve']);

    // Stock Management & History
    Route::get('/stock/history', [StockController::class, 'history']);
    Route::get('/stock/history/{itemId}', [StockController::class, 'itemHistory']);
    Route::post('/stock/add', [StockController::class, 'addStock']);
    Route::post('/stock/adjust', [StockController::class, 'adjustStock']);

    // User Management (Super Admin only via Gate Policy)
    Route::apiResource('users', UserController::class);
});
