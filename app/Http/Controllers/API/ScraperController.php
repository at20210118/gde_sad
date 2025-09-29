<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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


class ScraperController extends Controller{
    public function scrape(){
 try {
    set_time_limit(400);

    $output = null;
    $return_var = null;

    exec('node ' . base_path('scraper/scraper.js'), $output, $return_var);

    if ($return_var !== 0) {
        return response()->json(['error' => 'Greska u izvrsavanju skripte', 'output' => $output], 500);
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

     
        Event::firstOrCreate([
            'event' => $event['event'],
            'place' => $event['place'],
            'event_start' => $event['event_start'],
            'category_id' => $category->id,
            'location_id' => $location->id,
            'image'=>$event['image']??null,
        ]);
    }

    return response()->json(['message' => 'Uspesno ubaceni dogadjaji.']);
    }
    catch (\Throwable $e) {
        return response()->json([
            'error' => true,
            'message' => 'Doslo je do greske.',
            'details' => $e->getMessage(),
        ], 500);
    }
}
}