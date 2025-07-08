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
        try{
        $locations = Location::all();
        return response()->json($locations);
        }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri ucitavanju lokacija.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try{
        $validated = $request->validate([
            'adress' => 'required|string|max:255',
        ]);

        $location = Location::create($validated);

        return response()->json(['message' => 'Location created', 'location' => $location], 201);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri kreiranju lokacije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try{
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
        return response()->json($location);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri ucitavanju lokacije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        $validated = $request->validate([
            'adress' => 'sometimes|required|string|max:255',
        ]);

        $location->update($validated);

        return response()->json(['message' => 'Location updated', 'location' => $location]);
    } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri azuriranju lokacije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try{
        $location = Location::find($id);
        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }
         Event::where('location_id', $id)->update(['location_id' => null]);
        $location->delete();

        return response()->json(['message' => 'Location deleted']);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri brisanju lokacije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
