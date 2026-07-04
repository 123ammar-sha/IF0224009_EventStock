<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    public function recordTransaction(
        Item $item,
        int $userId,
        string $type,
        int $qtyChange,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $description = null
    ): StockTransaction {
        $qtyBefore = $item->available_qty - ($type === 'in' || $type === 'adjustment' ? $qtyChange : -$qtyChange);

        if ($type === 'out') {
            $qtyBefore = $item->available_qty + $qtyChange;
        }

        return StockTransaction::create([
            'item_id' => $item->id,
            'user_id' => $userId,
            'type' => $type,
            'qty_change' => $type === 'out' ? -$qtyChange : $qtyChange,
            'qty_before' => $qtyBefore,
            'qty_after' => $item->available_qty,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
        ]);
    }

    public function addStock(int $itemId, int $quantity, int $userId, ?string $description = null): Item
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $description) {
            $item = Item::where('id', $itemId)->lockForUpdate()->firstOrFail();

            $item->available_qty += $quantity;
            $item->total_qty += $quantity;
            if ($item->status === 'consumed' || $item->status === 'lost') {
                $item->status = 'available';
            }
            $item->save();

            $this->recordTransaction(
                $item,
                $userId,
                'in',
                $quantity,
                'purchase',
                null,
                $description ?? 'Pembelian/Restock barang'
            );

            return $item->fresh();
        });
    }

    public function deductStock(int $itemId, int $quantity, int $userId, ?string $description = null): Item
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $description) {
            $item = Item::where('id', $itemId)->lockForUpdate()->firstOrFail();

            if ($item->available_qty < $quantity) {
                throw new Exception("Stok {$item->name} tidak mencukupi. Tersedia: {$item->available_qty}, diminta: {$quantity}");
            }

            $item->available_qty -= $quantity;
            $item->total_qty -= $quantity;
            $item->save();

            $this->recordTransaction(
                $item,
                $userId,
                'out',
                $quantity,
                'adjustment',
                null,
                $description ?? 'Pengurangan stok (adjustment)'
            );

            return $item->fresh();
        });
    }

    public function adjustStock(int $itemId, int $newAvailableQty, int $userId, ?string $reason = null): Item
    {
        return DB::transaction(function () use ($itemId, $newAvailableQty, $userId, $reason) {
            $item = Item::where('id', $itemId)->lockForUpdate()->firstOrFail();
            $diff = $newAvailableQty - $item->available_qty;

            if ($diff === 0) {
                throw new Exception('Tidak ada perubahan stok.');
            }

            $item->available_qty = $newAvailableQty;
            // Jika adjustment positif, total_qty ikut naik; jika negatif, total_qty turun
            $item->total_qty += $diff;
            if ($item->total_qty < 0) $item->total_qty = 0;
            if ($item->available_qty < 0) $item->available_qty = 0;

            if ($item->available_qty <= 0 && $item->category && $item->category->type === 'consumable') {
                $item->status = 'consumed';
            } elseif ($item->available_qty > 0) {
                $item->status = 'available';
            }

            $item->save();

            $this->recordTransaction(
                $item,
                $userId,
                'adjustment',
                $diff,
                'adjustment',
                null,
                $reason ?? 'Penyesuaian stok manual'
            );

            return $item->fresh();
        });
    }

    public function getHistory(int $itemId, int $limit = 50)
    {
        return StockTransaction::with('user')
            ->where('item_id', $itemId)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function getAllHistory(array $filters = [], int $perPage = 20)
    {
        $query = StockTransaction::with(['item', 'user'])->latest();

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
}
