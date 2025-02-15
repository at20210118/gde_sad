<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function getEvents()
    {
        $events = DB::select('SELECT event, l.adress, c.name as category FROM events e join locations l on e.location_id=l.id join categories c on e.category_id=c.id');
        return response()->json($events);
    }
    public function getById ($id)
    {
        $event=DB::select('SELECT event FROM events where id=?',[$id]);
        return response()->json($event);
    }
}
