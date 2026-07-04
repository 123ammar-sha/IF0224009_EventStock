<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query()->latest();

        // Search (Cari berdasarkan nama atau venue)
        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('venue', 'like', "%{$search}%");
        });

        $query->when($request->status, function ($q, $status) {
            $statuses = explode(',', $status);
            $q->whereIn('status', $statuses);
        });

        // Pagination
        $perPage = $request->query('per_page', 10);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|in:upcoming,ongoing,completed,cancelled'
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event berhasil ditambahkan',
            'data' => $event
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => 'Event tidak ditemukan'], 404);

        return response()->json(['data' => $event]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => 'Event tidak ditemukan'], 404);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'venue' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'sometimes|in:upcoming,ongoing,completed,cancelled'
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Data Event berhasil diperbarui',
            'data' => $event
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::find($id);
        if (!$event) return response()->json(['message' => 'Event tidak ditemukan'], 404);

        try {
            $event->delete();
            return response()->json(['message' => 'Event berhasil dibatalkan dan dihapus']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus. Event ini sudah memiliki riwayat mutasi barang.',
            ], 422);
        }
    }
}
