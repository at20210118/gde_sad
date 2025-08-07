<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\EventController;
use App\Models\Event;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\LocationController;

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

Route::get('/scrape', function () {
    set_time_limit(400);

    $output = null;
    $return_var = null;

    exec('node ' . base_path('scraper/scraper.js'), $output, $return_var);

    if ($return_var !== 0) {
        return response()->json(['error' => 'Greška u izvršavanju skripte', 'output' => $output], 500);
    }

    $jsonString = implode("", $output);
    $events = json_decode($jsonString, true);

    if (!$events) {
        return response()->json(['error' => 'Neispravan JSON format ili prazan rezultat.'], 500);
    }

     \App\Models\Event::truncate();

    foreach ($events as $event) {
     
        $categoryName = $event['category'][0] ?? 'Ostalo';
        $category = Category::firstOrCreate(['name' => $categoryName]);

       
        $location = Location::firstOrCreate([
            'adress' => $event['location'],
        ]);

     
        Event::create([
            'event' => $event['event'],
            'place' => $event['place'],
            'event_start' => $event['event_start'],
            'category_id' => $category->id,
            'location_id' => $location->id,
        ]);
    }

    return response()->json(['message' => 'Uspesno ubaceni dogadjaji.']);
});

