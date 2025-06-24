<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Event;
class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        return response()->json($locations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'adress' => 'required|string|max:255',
        ]);

        $location = Location::create($validated);

        return response()->json(['message' => 'Location created', 'location' => $location], 201);
    }

    public function show($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
        return response()->json($location);
    }

    public function update(Request $request, $id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        $validated = $request->validate([
            'adress' => 'sometimes|required|string|max:255',
        ]);

        $location->update($validated);

        return response()->json(['message' => 'Location updated', 'location' => $location]);
    }

    public function destroy($id)
    {
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
         Event::where('location_id', $id)->update(['location_id' => null]);
        $location->delete();

        return response()->json(['message' => 'Location deleted']);
    }
}
