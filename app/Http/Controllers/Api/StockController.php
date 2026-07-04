<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;

class StockController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Riwayat transaksi stok (global).
     */
    public function history(Request $request)
    {
        $filters = $request->only(['item_id', 'type', 'date_from', 'date_to']);
        $perPage = $request->per_page ?? 20;

        $history = $this->stockService->getAllHistory($filters, $perPage);

        return response()->json([
            'message' => 'Riwayat transaksi stok berhasil diambil',
            'data' => $history
        ]);
    }

    /**
     * Riwayat transaksi untuk item tertentu.
     */
    public function itemHistory($itemId, Request $request)
    {
        $item = Item::findOrFail($itemId);
        $limit = $request->limit ?? 50;

        $history = $this->stockService->getHistory($itemId, $limit);

        return response()->json([
            'message' => "Riwayat stok untuk {$item->name} berhasil diambil",
            'data' => [
                'item' => $item,
                'transactions' => $history
            ]
        ]);
    }

    /**
     * Tambah stok (pembelian/restock).
     */
    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $item = $this->stockService->addStock(
                $validated['item_id'],
                $validated['quantity'],
                auth()->id(),
                $validated['description'] ?? null
            );

            return response()->json([
                'message' => "Stok {$item->name} berhasil ditambahkan. Stok tersedia: {$item->available_qty}",
                'data' => $item
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal menambah stok.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Adjustment stok (set manual ke nilai tertentu).
     */
    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'new_available_qty' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $item = $this->stockService->adjustStock(
                $validated['item_id'],
                $validated['new_available_qty'],
                auth()->id(),
                $validated['reason'] ?? null
            );

            return response()->json([
                'message' => "Stok {$item->name} telah disesuaikan menjadi {$item->available_qty}",
                'data' => $item
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal melakukan penyesuaian stok.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
