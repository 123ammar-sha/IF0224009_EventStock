<?php

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('events:update-status', function () {
    $today = Carbon::today()->format('Y-m-d');

    // 1. Ubah ke COMPLETED
    $completedCount = Event::whereIn('status', ['upcoming', 'ongoing'])
        ->whereDate('end_date', '<', $today)
        ->update(['status' => 'completed']);

    // 2. Ubah ke ONGOING
    $ongoingCount = Event::where('status', 'upcoming')
        ->whereDate('start_date', '<=', $today)
        ->whereDate('end_date', '>=', $today)
        ->update(['status' => 'ongoing']);

    $this->info("Berhasil memperbarui status event: {$completedCount} Completed, {$ongoingCount} Ongoing.");
})->purpose('Memperbarui status event otomatis berdasarkan tanggal hari ini')->daily();
