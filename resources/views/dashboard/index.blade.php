@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div x-data="dashboardPage()" x-init="init()">
    <!-- Metric cards baris pertama -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card: Tersedia -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm hover:-translate-y-1 hover:shadow-modern transition-all duration-300 border-l-4 border-l-emerald-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tersedia</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <i class="fas fa-check text-emerald-600 text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight" x-text="stats.inventory_summary?.available ?? '—'"></p>
            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> barang siap digunakan
            </p>
        </div>

        <!-- Card: Digunakan -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm hover:-translate-y-1 hover:shadow-modern transition-all duration-300 border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Digunakan</span>
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <i class="fas fa-truck text-blue-600 text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight" x-text="stats.inventory_summary?.on_duty ?? '—'"></p>
            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block animate-pulse"></span> sedang di lapangan
            </p>
        </div>

        <!-- Card: Perawatan -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm hover:-translate-y-1 hover:shadow-modern transition-all duration-300 border-l-4 border-l-amber-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Perawatan</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center">
                    <i class="fas fa-wrench text-amber-600 text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight" x-text="stats.inventory_summary?.maintenance ?? '—'"></p>
            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span> dalam perbaikan/pemeliharaan
            </p>
        </div>

        <!-- Card: Hilang -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm hover:-translate-y-1 hover:shadow-modern transition-all duration-300 border-l-4 border-l-rose-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Hilang</span>
                <div class="w-9 h-9 rounded-xl bg-rose-500/10 flex items-center justify-center">
                    <i class="fas fa-circle-xmark text-rose-600 text-sm"></i>
                </div>
            </div>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight" x-text="stats.inventory_summary?.lost ?? '—'"></p>
            <p class="text-[10px] text-slate-400 mt-1.5 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span> aset tidak kembali
            </p>
        </div>
    </div>

    <!-- Baris kedua: Chart + Info Events & Incidents -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Chart manifest 7 hari terakhir -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 lg:col-span-2 shadow-modern-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Aktivitas Manifest (7 Hari)</h3>
                <span class="text-[10px] bg-slate-50 text-slate-500 border border-slate-100 rounded-full px-2.5 py-1 font-semibold">Real-time update</span>
            </div>
            <div class="h-64">
                <canvas id="manifestChart"></canvas>
            </div>
        </div>

        <!-- Status Events & Alert Insiden Aktif -->
        <div class="flex flex-col gap-6">
            <!-- Status Acara Card -->
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm">
                <h3 class="font-bold text-slate-800 text-sm mb-4">Status Acara</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-500/25"></div>
                            <span class="text-xs font-semibold text-slate-500">Akan datang</span>
                        </div>
                        <span class="text-sm font-bold text-slate-700" x-text="stats.events?.upcoming ?? 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/25 animate-pulse"></div>
                            <span class="text-xs font-semibold text-slate-500">Berlangsung</span>
                        </div>
                        <span class="text-sm font-bold text-slate-700" x-text="stats.events?.ongoing ?? 0"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-slate-400 shadow-sm shadow-slate-400/25"></div>
                            <span class="text-xs font-semibold text-slate-500">Selesai</span>
                        </div>
                        <span class="text-sm font-bold text-slate-700" x-text="stats.events?.completed ?? 0"></span>
                    </div>
                </div>
            </div>

            <!-- Panel Insiden Aktif -->
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 text-sm">Insiden Perlu Perhatian</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                          :class="stats.incident_summary?.active_incidents_count > 0 ? 'bg-rose-500/10 text-rose-600' : 'bg-slate-100 text-slate-500'"
                          x-text="stats.incident_summary?.active_incidents_count ?? 0">
                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-3 max-h-[140px] pr-1">
                    <!-- Iterasi Insiden -->
                    <template x-for="inc in stats.incident_summary?.unresolved_incidents || []" :key="inc.id">
                        <div class="p-3 bg-rose-50/30 border border-rose-100/50 rounded-xl hover:bg-rose-50/50 transition-all flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-700 truncate max-w-[140px]" x-text="inc.manifest_item?.item?.name || 'Barang'"></span>
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                      :class="inc.type === 'lost' ? 'bg-rose-100 text-rose-800 border border-rose-200/40' : 'bg-amber-100 text-amber-800 border border-amber-200/40'"
                                      x-text="inc.type === 'lost' ? 'Hilang' : 'Rusak'"></span>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400">
                                <span class="truncate max-w-[140px]" x-text="inc.manifest_item?.manifest?.event?.name || 'Acara'"></span>
                                <span class="font-semibold text-rose-600" x-text="inc.quantity + ' Pcs'"></span>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State Insiden -->
                    <template x-if="!stats.incident_summary?.active_incidents_count">
                        <div class="h-full flex flex-col items-center justify-center text-center py-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center mb-2 shadow-sm shadow-emerald-500/5">
                                <i class="fas fa-shield-halved text-sm"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-700">Gudang Aman</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Tidak ada insiden aktif</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris ketiga: Manifest Terbaru (Left 2/3) + Agenda Terdekat (Right 1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Manifest terbaru -->
        <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden lg:col-span-2 shadow-modern-sm flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-white">
                <h3 class="font-bold text-slate-800 text-sm">Manifest Terbaru</h3>
                <a href="/manifests" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline flex items-center gap-1">
                    Lihat semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/50 text-[10px] text-slate-400 uppercase font-bold tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 text-left">No. Manifest</th>
                            <th class="px-5 py-3 text-left">Tipe</th>
                            <th class="px-5 py-3 text-left">Event</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <template x-for="m in stats.recent_manifests || []" :key="m.id">
                            <tr class="hover:bg-slate-50/30 transition-colors">
                                <td class="px-5 py-3 font-mono font-bold text-slate-800 text-xs" x-text="m.manifest_number"></td>
                                <td class="px-5 py-3">
                                    <span :class="m.type === 'outbound'
                                            ? 'text-indigo-600 bg-indigo-50/80 border border-indigo-100/50'
                                            : 'text-emerald-600 bg-emerald-50/80 border border-emerald-100/50'"
                                          class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                          x-text="m.type === 'outbound' ? 'Keluar' : 'Kembali'">
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs font-medium text-slate-700 truncate max-w-[150px]" x-text="m.event?.name || '—'"></td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border"
                                          :class="statusClass(m.status)">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="{
                                                  'bg-slate-400': m.status === 'draft',
                                                  'bg-blue-500 animate-pulse': m.status === 'in_progress',
                                                  'bg-emerald-500': m.status === 'completed',
                                                  'bg-rose-500': m.status === 'has_issue'
                                              }"></span>
                                        <span x-text="statusLabel(m.status)"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-400 text-xs" x-text="formatDate(m.created_at)"></td>
                            </tr>
                        </template>
                        <template x-if="!stats.recent_manifests || stats.recent_manifests.length === 0">
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada data manifest terbaru</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Agenda Acara Terdekat (Right 1/3) -->
        <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-modern-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Agenda Terdekat</h3>
                <a href="/events" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline">Semua</a>
            </div>
            
            <div class="flex-1 overflow-y-auto space-y-3.5 max-h-[290px] pr-1">
                <template x-for="ev in stats.agenda_events || []" :key="ev.id">
                    <div class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-50 hover:bg-slate-50/50 hover:border-slate-100 transition-all duration-200">
                        <!-- Date Badge -->
                        <div class="flex flex-col items-center justify-center w-11 h-11 rounded-lg bg-indigo-500/10 border border-indigo-100/30 text-indigo-600 shrink-0">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-500" x-text="new Date(ev.start_date).toLocaleDateString('id-ID', {month: 'short'})"></span>
                            <span class="text-sm font-extrabold leading-none text-indigo-700" x-text="new Date(ev.start_date).getDate()"></span>
                        </div>
                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 truncate" x-text="ev.name"></h4>
                            <p class="text-[10px] text-slate-400 mt-0.5 truncate flex items-center gap-1">
                                <i class="fas fa-location-dot text-[9px] text-slate-300"></i>
                                <span x-text="ev.venue || 'Lokasi belum diisi'"></span>
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider border"
                                      :class="ev.status === 'ongoing' 
                                            ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' 
                                            : 'bg-blue-500/10 text-blue-600 border-blue-500/20'"
                                      x-text="ev.status === 'ongoing' ? 'Berjalan' : 'Akan Datang'"></span>
                            </div>
                        </div>
                    </div>
                </template>
                
                <template x-if="!stats.agenda_events || stats.agenda_events.length === 0">
                    <div class="py-12 text-center text-slate-400 flex flex-col items-center justify-center gap-2 h-full">
                        <div class="w-10 h-10 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center">
                            <i class="far fa-calendar-times text-base"></i>
                        </div>
                        <p class="text-xs">Tidak ada agenda terdekat</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function dashboardPage() {
        return {
            stats: {},
            chartInstance: null,

            async init() {
                requireAuth();
                await this.fetchData();
            },

            async fetchData() {
                try {
                    const res = await apiRequest("GET", "/dashboard");
                    this.stats = res.data;
                    this.$nextTick(() => this.renderChart());
                } catch (e) {
                    toastError("Gagal memuat data dashboard");
                }
            },

            renderChart() {
                const ctx = document.getElementById("manifestChart");
                if (!ctx) return;

                if (this.chartInstance) this.chartInstance.destroy();

                const outboundCount = (this.stats.recent_manifests || []).filter((m) => m.type === "outbound").length;
                const inboundCount = (this.stats.recent_manifests || []).filter((m) => m.type === "inbound").length;

                const ctxCanvas = ctx.getContext("2d");
                
                // Gradient untuk Outbound (Indigo ke Light Indigo)
                const gradOutbound = ctxCanvas.createLinearGradient(0, 0, 0, 240);
                gradOutbound.addColorStop(0, '#6366f1'); // indigo-500
                gradOutbound.addColorStop(1, '#a5b4fc'); // indigo-300
                
                // Gradient untuk Inbound (Emerald ke Mint)
                const gradInbound = ctxCanvas.createLinearGradient(0, 0, 0, 240);
                gradInbound.addColorStop(0, '#10b981'); // emerald-500
                gradInbound.addColorStop(1, '#6ee7b7'); // emerald-300

                this.chartInstance = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
                        datasets: [
                            {
                                label: "Keluar",
                                data: [0, 0, 0, 0, 0, 0, outboundCount],
                                backgroundColor: gradOutbound,
                                borderRadius: 8,
                                borderSkipped: false,
                                barThickness: 16,
                            },
                            {
                                label: "Kembali",
                                data: [0, 0, 0, 0, 0, 0, inboundCount],
                                backgroundColor: gradInbound,
                                borderRadius: 8,
                                borderSkipped: false,
                                barThickness: 16,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { 
                                position: "bottom",
                                labels: {
                                    boxWidth: 10,
                                    boxHeight: 10,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 15,
                                    font: {
                                        family: "'Plus Jakarta Sans', sans-serif",
                                        size: 11,
                                        weight: '600'
                                    },
                                    color: '#64748b'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11, weight: '700' },
                                bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: true,
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                ticks: { 
                                    precision: 0,
                                    color: '#94a3b8',
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }
                                },
                                grid: {
                                    color: '#f1f5f9',
                                    drawBorder: false
                                }
                            },
                            x: { 
                                ticks: {
                                    color: '#94a3b8',
                                    font: { family: "'Plus Jakarta Sans', sans-serif", size: 10 }
                                },
                                grid: { display: false, drawBorder: false } 
                            },
                        },
                    },
                });
            },

            statusClass(status) {
                const map = {
                    draft: "bg-slate-50 text-slate-600 border-slate-200/60",
                    in_progress: "bg-blue-50/50 text-blue-700 border-blue-100/50",
                    completed: "bg-emerald-50/50 text-emerald-700 border-emerald-100/50",
                    has_issue: "bg-rose-50/50 text-rose-700 border-rose-100/50",
                };
                return map[status] || "bg-slate-50 text-slate-600 border-slate-200/60";
            },

            statusLabel(status) {
                const map = {
                    draft: "Draft",
                    in_progress: "Berjalan",
                    completed: "Selesai",
                    has_issue: "Ada Masalah",
                };
                return map[status] || status;
            },

            formatDate(dateStr) {
                if (!dateStr) return "—";
                return new Date(dateStr).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "short",
                    hour: "2-digit",
                    minute: "2-digit",
                });
            },
        };
    }
</script>
@endpush
