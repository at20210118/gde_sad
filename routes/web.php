<?php
use App\Models\Event;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;


Route::get('/', [EventController::class,'index']);
