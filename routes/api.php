<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\GoldPriceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Categories autocomplete
    Route::get('/categories/suggest', [CategoryController::class, 'suggest'])->name('api.categories.suggest');

    // Gold price
    Route::get('/gold-price/latest', [GoldPriceController::class, 'latest'])->name('api.gold-price.latest');
});
