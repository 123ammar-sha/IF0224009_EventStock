<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\IncidentLog;
use App\Models\Item;
use App\Models\Manifest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $itemStats = Item::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeIncidents = IncidentLog::with(['manifestItem.item', 'manifestItem.manifest.event'])
            ->where('resolved', false)
            ->get();

        $activeEvents = Event::where('status', 'ongoing')->get();

        $recentManifests = Manifest::with('event')->latest()->take(5)->get();

        $agendaEvents = Event::whereIn('status', ['upcoming', 'ongoing'])
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'message' => 'Data Dashboard berhasil diambil',
            'data' => [
                'inventory_summary' => [
                    'available' => $itemStats['available'] ?? 0,
                    'on_duty' => $itemStats['on_duty'] ?? 0,
                    'maintenance' => $itemStats['maintenance'] ?? 0,
                    'lost' => $itemStats['lost'] ?? 0,
                ],
                'incident_summary' => [
                    'active_events_count' => $activeEvents->count(),
                    'active_incidents_count' => $activeIncidents->count(),
                    'unresolved_incidents' => $activeIncidents,
                ],
                'events' => [
                    'upcoming'  => Event::where('status', 'upcoming')->count(),
                    'ongoing'   => Event::where('status', 'ongoing')->count(),
                    'completed' => Event::where('status', 'completed')->count(),
                ],
                'recent_manifests' => $recentManifests,
                'agenda_events' => $agendaEvents,
            ]
        ]);
    }
}
