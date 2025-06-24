<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Category;
use App\Models\Location;


class EventController extends Controller
{
    public function index()
    {
        $events = DB::select('SELECT event, l.adress, c.name as category FROM events e join locations l on e.location_id=l.id join categories c on e.category_id=c.id');
        return response()->json($events);
    }
    public function show ($id)
    {
        $event=DB::select('SELECT e.place,event, e.event_start,l.adress, c.name as category FROM events e join locations l on e.location_id=l.id join categories c on e.category_id=c.id where e.id=?',[$id]);
        return response()->json($event);
    }
    public function destroy($id)
    {
    $deleted = DB::delete('DELETE FROM events WHERE id = ?', [$id]);

    if ($deleted) {
        return response()->json(['message' => 'Događaj obrisan']);
    } else {
        return response()->json(['error' => 'Događaj nije pronađen'], 404);
    }
    }
  
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event' => 'required|string|max:255',
            'place' => 'required|string|max:255',
            'event_start' => 'required|date',
            'category' => 'required|string',
            'location' => 'required|string',
        ]);

        $category = Category::firstOrCreate(['name' => $validated['category']]);
        $location = Location::firstOrCreate(['adress' => $validated['location']]);

        $event = Event::create([
            'event' => $validated['event'],
            'place' => $validated['place'],
            'event_start' => $validated['event_start'],
            'category_id' => $category->id,
            'location_id' => $location->id,
        ]);

        return response()->json(['message' => 'Događaj kreiran', 'event' => $event], 201);
    }

 
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'event' => 'sometimes|required|string|max:255',
            'place' => 'sometimes|required|string|max:255',
            'event_start' => 'sometimes|required|date',
            'category' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
        ]);

        if (isset($validated['category'])) {
            $category = Category::firstOrCreate(['name' => $validated['category']]);
            $event->category_id = $category->id;
        }

        if (isset($validated['location'])) {
            $location = Location::firstOrCreate(['adress' => $validated['location']]);
            $event->location_id = $location->id;
        }

        $event->fill($validated);
        $event->save();

        return response()->json(['message' => 'Događaj ažuriran', 'event' => $event]);
    }
}
