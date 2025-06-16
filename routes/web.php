<?php
use App\Models\Event;
use Illuminate\Support\Facades\Route;


Route::get('/', [EventController::class,'getEvents']);
