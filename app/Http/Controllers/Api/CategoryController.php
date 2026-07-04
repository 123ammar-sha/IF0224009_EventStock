<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::latest();
        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'like', '%' . $search . '%');
        });
        $categories = $query->paginate(10);
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,consumable'
        ]);
        return response()->json(['data' => Category::create($validated)], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cat = Category::findOrFail($id);
        return response()->json(['data' => $cat]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cat = Category::findOrFail($id);
        $cat->update($request->validate(['name' => 'sometimes|string', 'type' => 'sometimes|in:asset,consumable']));
        return response()->json(['data' => $cat]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            Category::findOrFail($id)->delete();
            return response()->json(['message' => 'Kategori dihapus']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kategori sedang digunakan oleh barang lain'], 422);
        }
    }
}
