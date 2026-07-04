<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Manifest;
use App\Services\ManifestService;
use Exception;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    protected $manifestService;

    public function __construct(ManifestService $manifestService)
    {
        $this->manifestService = $manifestService;
    }

    public function index(Request $request)
    {
        $query = Manifest::with(['event', 'user', 'manifestItems.item']);

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 15;
        $manifests = $query->latest()->paginate($perPage);

        return response()->json([
            'message' => 'Daftar manifest berhasil diambil',
            'data' => $manifests
        ]);
    }

    public function show($id)
    {
        $manifest = Manifest::with([
            'event',
            'user',
            'manifestItems.item.category',
            'manifestItems.incidentLog'
        ])->findOrFail($id);

        return response()->json([
            'message' => 'Detail manifest berhasil diambil',
            'data' => $manifest
        ]);
    }

    public function storeOutbound(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',

            // Flightcases dinonaktifkan untuk rilis ini
        ]);

        try {
            $manifest = $this->manifestService->createOutboundManifest($validated, auth()->id());

            return response()->json([
                'message' => 'Manifest Outbound berhasil dibuat, stok telah dikurangi.',
                'data' => $manifest
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memproses manifest.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function storeInbound(Request $request)
    {
        $validated = $request->validate([
            'outbound_manifest_id' => 'required|exists:manifests,id',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty_actual' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:good,broken,lost',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $manifest = $this->manifestService->processInboundManifest($validated, auth()->id());

            return response()->json([
                'message' => 'Manifest Inbound berhasil diproses. Audit stok telah disesuaikan.',
                'data' => $manifest
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal memproses manifest kepulangan.',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
