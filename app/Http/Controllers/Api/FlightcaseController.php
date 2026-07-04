<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Flightcase;
use App\Models\Item;
use Illuminate\Http\Request;

class FlightcaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Flightcase::withCount('bundledItems as items_count');

        if ($request->has('has_items')) {
            $query->whereHas('bundledItems');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $perPage = $request->per_page ?? 10;
        $flightcases = $query->paginate($perPage);

        // For picker: include items data
        if ($request->has('for_picker') || $request->has('has_items')) {
            $flightcases->load([
                'items:id,name,sku,available_qty,total_qty,flightcase_id',
                'bundledItems:id,name,sku,available_qty,total_qty'
            ]);
        }

        // Format response sesuai frontend (tanpa meta wrapper)
        return response()->json([
            'data' => $flightcases->items(),
            'current_page' => $flightcases->currentPage(),
            'last_page' => $flightcases->lastPage(),
            'per_page' => $flightcases->perPage(),
            'total' => $flightcases->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:flightcases,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $flightcase = Flightcase::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Attach items to pivot table
        if (!empty($validated['items'])) {
            $pivotData = [];
            foreach ($validated['items'] as $item) {
                $pivotData[$item['item_id']] = ['qty' => $item['quantity']];
            }
            $flightcase->bundledItems()->attach($pivotData);
        }

        return response()->json([
            'message' => 'Flightcase berhasil dibuat',
            'data' => $flightcase->load('bundledItems')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $fc = Flightcase::with('bundledItems')->findOrFail($id);

        // Format response sesuai frontend
        return response()->json([
            'id' => $fc->id,
            'code' => $fc->code,
            'name' => $fc->name,
            'description' => $fc->description,
            'created_at' => $fc->created_at,
            'updated_at' => $fc->updated_at,
            'items' => $fc->bundledItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->pivot->qty ?? 1,
                    'available_qty' => $item->available_qty,
                    'total_qty' => $item->total_qty,
                    'status' => $item->status,
                    'category_id' => $item->category_id,
                ];
            })
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $fc = Flightcase::findOrFail($id);

        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:flightcases,code,' . $id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required_with:items|exists:items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $updateData = [];
        if (isset($validated['code'])) $updateData['code'] = $validated['code'];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (array_key_exists('description', $validated)) $updateData['description'] = $validated['description'];
        $fc->update($updateData);

        // Sync items to pivot table
        if (isset($validated['items'])) {
            $pivotData = [];
            foreach ($validated['items'] as $item) {
                $pivotData[$item['item_id']] = ['qty' => $item['quantity']];
            }
            $fc->bundledItems()->sync($pivotData);
        }

        return response()->json([
            'message' => 'Flightcase berhasil diperbarui',
            'data' => $fc->load('bundledItems')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $fc = Flightcase::findOrFail($id);
        // Detach all items from pivot
        $fc->bundledItems()->detach();
        $fc->delete();
        return response()->json(['message' => 'Flightcase dihapus']);
    }
}
