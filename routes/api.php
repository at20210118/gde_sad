<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\API\LocationController;
use App\Models\Event;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ScraperController;


Route::get('/users', function () {
    return response()->json([
        'users' => \App\Models\User::all(),
    ]);
});
Route::middleware(['auth:sanctum',  \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::post('/locations', [LocationController::class, 'store']);
    Route::put('/locations/{location}', [LocationController::class, 'update']);
    Route::delete('/locations/{location}', [LocationController::class, 'destroy']);
    
    Route::get('/scrape',[ScraperController::class,'scrape']);
});



Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/events/category/{categoryName}', [EventController::class, 'showByCategory']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/locations', [LocationController::class, 'index']);
Route::get('/scrape',[ScraperController::class,'scrape']);
