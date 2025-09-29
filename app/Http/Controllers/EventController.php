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
        try {
            $perPage = 10;
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $offset = ($page - 1) * $perPage;

            $total = DB::table('events')->count();

            $events = DB::select(
                'SELECT e.id, e.place, e.event, e.event_start, e.image, l.adress, c.name as category
                 FROM events e 
                 JOIN locations l ON e.location_id = l.id 
                 JOIN categories c ON e.category_id = c.id 
                 ORDER BY e.event_start ASC 
                 LIMIT ? OFFSET ?', 
                [$perPage, $offset]
            );

            return response()->json([
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'data' => $events,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom dohvatanja dogadjaja.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = DB::select(
                'SELECT e.id, e.place, e.event, e.event_start, e.image, l.adress, c.name as category 
                 FROM events e 
                 JOIN locations l ON e.location_id = l.id 
                 JOIN categories c ON e.category_id = c.id 
                 WHERE e.id = ?', 
                [$id]
            );
            return response()->json($event);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom prikaza dogadjaja.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $deleted = DB::delete('DELETE FROM events WHERE id = ?', [$id]);

            if ($deleted) {
                return response()->json(['message' => 'Događaj obrisan']);
            } else {
                return response()->json(['error' => 'Događaj nije pronađen'], 404);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom brisanja dogadjaja.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'event' => 'required|string|max:255',
                'place' => 'required|string|max:255',
                'event_start' => 'required|date',
                'category' => 'required|string',
                'location' => 'required|string',
                'image' => 'nullable|string|max:255',
            ]);

            $category = Category::firstOrCreate(['name' => $validated['category']]);
            $location = Location::firstOrCreate(['adress' => $validated['location']]);

            $event = Event::firstOrCreate([
                'event' => $validated['event'],
                'place' => $validated['place'],
                'event_start' => $validated['event_start'],
                'image' => $validated['image'] ?? null,
                'category_id' => $category->id,
                'location_id' => $location->id,
            ]);

            return response()->json(['message' => 'Događaj kreiran', 'event' => $event], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom kreiranja dogadjaja.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);

            $validated = $request->validate([
                'event' => 'sometimes|required|string|max:255',
                'place' => 'sometimes|required|string|max:255',
                'event_start' => 'sometimes|required|date',
                'category' => 'sometimes|required|string',
                'location' => 'sometimes|required|string',
                'image' => 'nullable|string|max:255',
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
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom ažuriranja dogadjaja.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function showByCategory($categoryName)
    {
        try {
            $category = Category::where('name', $categoryName)->first();

            if (!$category) {
                return response()->json(['message' => 'Kategorija nije pronađena'], 404);
            }

            $event = DB::select(
                'SELECT e.id, e.place, e.event, e.event_start, e.image, l.adress, c.name as category 
                 FROM events e 
                 JOIN locations l ON e.location_id = l.id 
                 JOIN categories c ON e.category_id = c.id 
                 WHERE e.category_id = ?', 
                [$category->id]
            );

            return response()->json($event);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska prilikom prikaza dogadjaja po kategoriji.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
