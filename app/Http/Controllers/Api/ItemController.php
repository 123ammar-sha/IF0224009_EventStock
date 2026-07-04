<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    public function index(Request $request)
    {
        $query = Item::with('category')->latest();

        $query->when($request->search, function ($q, $search) {
            $q->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        });

        $query->when($request->category_id, function ($q, $categoryId) {
            $q->where('category_id', $categoryId);
        });

        $query->when($request->status, function ($q, $status) {
            $q->where('status', $status);
        });


        $perPage = $request->per_page ? $request->per_page : 10;
        $items = $query->paginate($perPage);

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'total' => $items->total(),
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'flightcase_id' => 'nullable|exists:flightcases,id',
            'sku' => 'required|string|unique:items,sku',
            'name' => 'required|string|max:255',
            'total_qty' => 'required|integer|min:1',
        ]);
        $validated['available_qty'] = $validated['total_qty'];
        $validated['status'] = 'available';

        $item = Item::create($validated);

        return response()->json([
            'message' => 'Barang baru berhasil ditambahkan ke inventaris',
            'data' => $item
        ], 201);
    }

    public function show(string $id)
    {
        $item = Item::with(['category', 'flightcase'])->find($id);

        if (!$item) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        return response()->json(['data' => $item]);
    }

    public function update(Request $request, string $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Barang tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'flightcase_id' => 'nullable|exists:flightcases,id',
            'sku' => 'sometimes|string|unique:items,sku,' . $id,
            'name' => 'sometimes|string|max:255',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Data barang berhasil diperbarui',
            'data' => $item
        ]);
    }

    public function destroy(string $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return response()->json(['message' => 'Barang tidak ditemukan']);
        }

        try {
            $item->delete();
            return response()->json(['message' => 'Barang berhasil dihapus dari sistem']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus barang. Barang ini memiliki riwayat transaksi logistik.',
            ], 422);
        }
    }
}
