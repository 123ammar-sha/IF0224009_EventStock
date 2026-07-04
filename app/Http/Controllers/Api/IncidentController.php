<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncidentLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\StockService;

class IncidentController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }
    public function index(Request $request)
    {
        $query = IncidentLog::with([
            'manifestItem.item',
            'manifestItem.manifest.event',
            'manifestItem.manifest.user'
        ]);

        // Filter by type (broken/lost)
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by resolved status
        if ($request->has('resolved')) {
            $query->where('resolved', $request->resolved === 'true' ? 1 : 0);
        }

        // Date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 15;
        $incidents = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Daftar insiden berhasil diambil',
            'data' => $incidents
        ]);
    }

    public function show($id)
    {
        $incident = IncidentLog::with([
            'manifestItem.item',
            'manifestItem.manifest.event',
            'manifestItem.manifest.user'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detail insiden berhasil diambil',
            'data' => $incident
        ]);
    }

    public function resolve(Request $request, $id)
    {
        $validated = $request->validate([
            'qty_resolved' => 'required|integer|min:0',
            'qty_unresolved' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $incident = IncidentLog::with(['manifestItem.item'])->findOrFail($id);

            if ($incident->resolved) {
                return response()->json([
                    'message' => 'Insiden sudah selesai ditangani'
                ], 422);
            }

            $qtyResolved = $validated['qty_resolved'];
            $qtyUnresolved = $validated['qty_unresolved'];

            if (($qtyResolved + $qtyUnresolved) !== $incident->qty_affected) {
                return response()->json([
                    'message' => 'Total qty_resolved dan qty_unresolved harus sama dengan qty_affected (' . $incident->qty_affected . ')'
                ], 422);
            }

            $item = $incident->manifestItem->item;

            \Log::info('Resolving incident:', [
                'incident_id' => $id,
                'type' => $incident->type,
                'qty_affected' => $incident->qty_affected,
                'qty_resolved' => $qtyResolved,
                'qty_unresolved' => $qtyUnresolved,
                'item_id' => $item->id,
                'before_total_qty' => $item->total_qty,
                'before_available_qty' => $item->available_qty,
            ]);

            // KEMBALIKAN STOK
            if ($incident->type === 'broken') {
                // Rusak:
                // qty_resolved = berhasil diperbaiki -> kembalikan ke available_qty
                if ($qtyResolved > 0) {
                    $item->increment('available_qty', $qtyResolved);
                    $this->stockService->recordTransaction(
                        $item, auth()->id(), 'in', $qtyResolved, 'incident', $incident->id,
                        'Barang rusak berhasil diperbaiki'
                    );
                }
                
                // qty_unresolved = rusak permanen -> kurangi dari total_qty
                if ($qtyUnresolved > 0) {
                    $item->decrement('total_qty', $qtyUnresolved);
                    $this->stockService->recordTransaction(
                        $item, auth()->id(), 'out', $qtyUnresolved, 'incident', $incident->id,
                        'Barang rusak tidak dapat diperbaiki (dibuang)'
                    );
                }

                if ($item->status === 'maintenance' && $item->available_qty > 0) {
                    $item->update(['status' => 'available']);
                }
            } elseif ($incident->type === 'lost') {
                // Hilang:
                // qty_resolved = ditemukan -> kembalikan ke total_qty dan available_qty
                if ($qtyResolved > 0) {
                    $item->increment('total_qty', $qtyResolved);
                    $item->increment('available_qty', $qtyResolved);
                    $this->stockService->recordTransaction(
                        $item, auth()->id(), 'in', $qtyResolved, 'incident', $incident->id,
                        'Barang hilang berhasil ditemukan'
                    );
                }
                
                // qty_unresolved = hilang permanen -> sudah dipotong saat inbound, jadi tidak mengubah stok lagi
                if ($qtyUnresolved > 0) {
                    $this->stockService->recordTransaction(
                        $item, auth()->id(), 'out', $qtyUnresolved, 'incident', $incident->id,
                        'Barang dikonfirmasi hilang permanen'
                    );
                }

                if ($item->status === 'lost' && $item->available_qty > 0) {
                    $item->update(['status' => 'available']);
                }
            }

            $item->refresh();

            \Log::info('After resolve:', [
                'after_total_qty' => $item->total_qty,
                'after_available_qty' => $item->available_qty,
            ]);

            $incident->update([
                'resolved' => 1,
                'qty_resolved' => $qtyResolved,
                'qty_unresolved' => $qtyUnresolved,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Insiden berhasil ditandai selesai, stok diperbarui',
                'data' => $incident->load(['manifestItem.item', 'manifestItem.manifest.event'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error resolving incident:', [
                'incident_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal menyelesaikan insiden',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Hapus insiden (jika belum resolved)
     */
    public function destroy($id)
    {
        $incident = IncidentLog::findOrFail($id);

        // Jika sudah resolved, jangan hapus
        if ($incident->resolved) {
            return response()->json([
                'message' => 'Insiden yang sudah selesai tidak bisa dihapus'
            ], 422);
        }

        $incident->delete();

        return response()->json([
            'message' => 'Insiden berhasil dihapus'
        ]);
    }
}
