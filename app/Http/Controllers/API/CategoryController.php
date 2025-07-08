<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Event;

class CategoryController extends Controller
{
    public function index()
    { try{
        $categories = Category::all();
        return response()->json($categories);
     } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri ucitavanju kategorija.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try{
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create($validated);

        return response()->json(['message' => 'Category created','category' => [
            'id' => $category->id,
            'name' => $category->name,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
        ]
        ], 201);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri kreiranju kategorije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try{
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json($category);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri pronalazenju kategorije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try{
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $id,
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Category updated', 'category' => $category]);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri azuriranju kategorije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try{
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        Event::where('category_id', $id)->update(['category_id' => null]);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri brisanju kategorije.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
