<?php

namespace App\Services;

use App\Models\IncidentLog;
use App\Models\Manifest;
use App\Models\ManifestItem;
use App\Models\Item;
use App\Models\Flightcase;
use Illuminate\Support\Facades\DB;
use Exception;

class ManifestService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Proses outbound manifest dengan dukungan penuh:
     * - Item satuan (dengan qty spesifik)
     * - Flightcase bundling (menggunakan pivot table flightcase_item untuk quantity)
     * - Asset vs Consumable handling
     * - Riwayat stok otomatis
     */
    public function createOutboundManifest(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Buat Header Transaksi
            $manifest = Manifest::create([
                'manifest_number' => 'OUT-' . date('YmdHis') . '-' . rand(100, 999),
                'event_id' => $data['event_id'],
                'user_id' => $userId,
                'type' => 'outbound',
                'status' => 'in_progress',
                'destination' => $data['destination'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $totalItemsOut = 0;

            // 2A. Proses Item Satuan
            if (isset($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $item = Item::where('id', $itemData['item_id'])->lockForUpdate()->firstOrFail();

                    if ($item->available_qty < $itemData['qty']) {
                        throw new Exception("Stok {$item->name} tidak mencukupi. Tersedia: {$item->available_qty}");
                    }

                    $qtyBefore = $item->available_qty;

                    ManifestItem::create([
                        'manifest_id' => $manifest->id,
                        'item_id' => $item->id,
                        'qty_requested' => $itemData['qty'],
                        'qty_actual' => $itemData['qty'],
                        'condition' => 'good',
                    ]);

                    $item->available_qty -= $itemData['qty'];
                    $totalItemsOut += $itemData['qty'];

                    // Consumable: total_qty juga berkurang (habis pakai)
                    if ($item->category && $item->category->type === 'consumable') {
                        $item->total_qty -= $itemData['qty'];
                        if ($item->total_qty < 0) $item->total_qty = 0;
                        if ($item->available_qty <= 0) {
                            $item->status = 'consumed';
                        }
                    } else {
                        $item->status = 'on_duty';
                    }

                    $item->save();

                    // Catat riwayat transaksi stok
                    $this->stockService->recordTransaction(
                        $item,
                        $userId,
                        'out',
                        $itemData['qty'],
                        'manifest',
                        $manifest->id,
                        "Outbound via manifest {$manifest->manifest_number}"
                    );
                }
            }

            // 2B. Proses Flightcase Bundling (menggunakan pivot table)
            if (isset($data['flightcases'])) {
                foreach ($data['flightcases'] as $fcData) {
                    $flightcase = Flightcase::with('bundledItems')->findOrFail($fcData['flightcase_id']);

                    // Ambil dari pivot table bundledItems (many-to-many dengan qty)
                    $bundledItems = $flightcase->bundledItems;

                    if ($bundledItems->isEmpty()) {
                        throw new Exception("Flightcase '{$flightcase->name}' kosong. Tidak ada barang terdaftar di dalamnya.");
                    }

                    foreach ($bundledItems as $bundledItem) {
                        $pivotQty = $bundledItem->pivot->qty ?? $bundledItem->total_qty;
                        $item = Item::where('id', $bundledItem->id)->lockForUpdate()->firstOrFail();

                        // Validasi stok: stok harus cukup untuk jumlah yang dibutuhkan
                        if ($item->available_qty < $pivotQty) {
                            throw new Exception(
                                "Stok '{$item->name}' di flightcase '{$flightcase->name}' tidak mencukupi. " .
                                    "Dibutuhkan: {$pivotQty}, Tersedia: {$item->available_qty}"
                            );
                        }

                        // Catat seluruh isi kotak ke dalam surat jalan
                        ManifestItem::create([
                            'manifest_id' => $manifest->id,
                            'item_id' => $item->id,
                            'qty_requested' => $pivotQty,
                            'qty_actual' => $pivotQty,
                            'condition' => 'good',
                        ]);

                        $item->available_qty -= $pivotQty;
                        $item->status = 'on_duty';
                        $item->save();

                        $totalItemsOut += $pivotQty;

                        // Catat riwayat transaksi stok
                        $this->stockService->recordTransaction(
                            $item,
                            $userId,
                            'out',
                            $pivotQty,
                            'manifest',
                            $manifest->id,
                            "Outbound via flightcase '{$flightcase->name}' (manifest {$manifest->manifest_number})"
                        );
                    }
                }
            }

            if ($totalItemsOut === 0) {
                throw new Exception('Tidak ada barang yang diproses. Kirim items atau flightcases.');
            }

            return $manifest->load(['manifestItems.item', 'event', 'user']);
        });
    }

    /**
     * Proses inbound manifest (pengembalian barang).
     * - Validasi cegah double inbound dengan pessimistic locking
     * - Cegah pengembalian barang consumable
     * - Deteksi broken/lost → buat insiden
     * - Catat riwayat stok
     */
    public function processInboundManifest(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            // 1. Ambil outbound manifest dengan lock untuk cegah race condition
            $outbound = Manifest::where('id', $data['outbound_manifest_id'])
                ->lockForUpdate()
                ->firstOrFail();

            // ===== VALIDASI: Cegah Double Inbound (dengan lock) =====
            if (in_array($outbound->status, ['completed', 'has_issue'])) {
                throw new Exception(
                    "Manifest outbound {$outbound->manifest_number} sudah diproses inbound sebelumnya. " .
                        "Status saat ini: {$outbound->status}"
                );
            }

            // 2. Buat Header Transaksi Kepulangan (Inbound)
            $inbound = Manifest::create([
                'manifest_number' => 'INB-' . date('YmdHis') . '-' . rand(100, 999),
                'event_id' => $outbound->event_id,
                'user_id' => $userId,
                'type' => 'inbound',
                'status' => 'completed',
                'outbound_manifest_id' => $outbound->id,
                'destination' => $data['destination'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $hasIssue = false;
            $incidents = [];

            // 3. Looping hasil checklist kru di lapangan
            foreach ($data['items'] as $returnedItem) {
                $item = Item::where('id', $returnedItem['item_id'])->lockForUpdate()->firstOrFail();

                // ===== VALIDASI: Cegah Inbound untuk Barang Consumable =====
                $item->load('category');
                if ($item->category && $item->category->type === 'consumable') {
                    throw new Exception(
                        "Barang '{$item->name}' adalah consumable (habis pakai) dan tidak dapat dikembalikan ke gudang."
                    );
                }

                // Simpan ke detail Inbound Manifest
                $manifestItem = ManifestItem::create([
                    'manifest_id' => $inbound->id,
                    'item_id' => $item->id,
                    'qty_requested' => 0,
                    'qty_actual' => $returnedItem['qty_actual'],
                    'condition' => $returnedItem['condition'],
                    'notes' => $returnedItem['notes'] ?? null,
                ]);

                // 4. Logika Audit & Mutasi Stok
                if ($returnedItem['condition'] === 'good') {
                    $item->available_qty += $returnedItem['qty_actual'];
                    $item->status = 'available';

                    // Catat riwayat stok masuk
                    $this->stockService->recordTransaction(
                        $item,
                        $userId,
                        'in',
                        $returnedItem['qty_actual'],
                        'manifest',
                        $outbound->id,
                        "Inbound good via manifest {$inbound->manifest_number}"
                    );
                } else {
                    $hasIssue = true;
                    $item->status = $returnedItem['condition'] === 'broken' ? 'maintenance' : 'lost';

                    if ($returnedItem['condition'] === 'lost') {
                        $item->total_qty -= $returnedItem['qty_actual'];
                        if ($item->total_qty < 0) $item->total_qty = 0;
                    }

                    // Catat riwayat untuk barang rusak/hilang
                    $incidentNotes = $returnedItem['notes'] ?? 'Tidak ada catatan';
                    $this->stockService->recordTransaction(
                        $item,
                        $userId,
                        'out',
                        $returnedItem['qty_actual'],
                        'incident',
                        null,
                        "Inbound {$returnedItem['condition']}: {$incidentNotes}"
                    );

                    // Buat insiden
                    $incident = IncidentLog::create([
                        'manifest_item_id' => $manifestItem->id,
                        'type' => $returnedItem['condition'],
                        'qty_affected' => $returnedItem['qty_actual'],
                        'resolved' => false,
                    ]);

                    $incidents[] = $incident;
                }

                $item->save();
            }

            // 5. Update status outbound
            $outbound->update(['status' => $hasIssue ? 'has_issue' : 'completed']);

            return $inbound->load(['manifestItems.item', 'manifestItems.incidentLog', 'event', 'user']);
        });
    }
}
