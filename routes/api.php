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

Route::apiResource('events', EventController::class);



Route::apiResource('categories', CategoryController::class);

Route::apiResource('locations', LocationController::class);


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/events/category/{categoryName}', [EventController::class, 'showByCategory']);
});

Route::get('/scrape',[ScraperController::class,'scrape']);