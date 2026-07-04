@extends('layouts.app')
@section('title', 'Manifest')
@section('page-title', 'Daftar Manifest')
@section('content')
    <div x-data="manifestsPage()" x-init="init()">
        <!-- Filter bar -->
        <div class="flex flex-wrap gap-3 mb-5">
            <select x-model="filterType" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                <option value="outbound">Keluar</option>
                <option value="inbound">Kembali</option>
            </select>

            <select x-model="filterStatus" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="in_progress">Berjalan</option>
                <option value="completed">Selesai</option>
                <option value="has_issue">Ada Masalah</option>
            </select>

            <input type="date" x-model="dateFrom" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            <input type="date" x-model="dateTo" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />

            <div class="ml-auto flex gap-2">
                <a href="/manifests/outbound"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white
                      rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-arrow-right-from-bracket text-xs"></i>
                    Barang Keluar
                </a>
                <a href="/manifests/inbound"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white
                      rounded-lg text-sm hover:bg-emerald-700">
                    <i class="fas fa-arrow-right-to-bracket text-xs"></i>
                    Barang Kembali
                </a>
            </div>
        </div>

        <!-- Tabel manifest -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
            </div>

            <div x-show="!loading && items.length === 0"
                class="flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="fas fa-clipboard text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Belum ada manifest</p>
            </div>

            <div x-show="!loading && items.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">No. Manifest</th>
                            <th class="px-5 py-3 text-left">Tipe</th>
                            <th class="px-5 py-3 text-left">Event</th>
                            <th class="px-5 py-3 text-left">Tujuan</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Dibuat Oleh</th>
                            <th class="px-5 py-3 text-left">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="m in items" :key="m.id">
                            <tr class="hover:bg-gray-50 cursor-pointer" @click="viewDetail(m.id)">
                                <td class="px-5 py-3.5 font-mono font-medium text-indigo-600" x-text="m.manifest_number">
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        :class="m.type === 'outbound' ?
                                            'bg-blue-100 text-blue-700' :
                                            'bg-emerald-100 text-emerald-700'"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-text="m.type==='outbound' ? 'Keluar' : 'Kembali'">
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-700" x-text="m.event?.name || '—'"></td>
                                <td class="px-5 py-3.5 text-gray-500" x-text="m.destination || '—'"></td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="statusClass(m.status)" x-text="statusLabel(m.status)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-500" x-text="m.user?.name || '—'"></td>
                                <td class="px-5 py-3.5 text-gray-400 text-xs" x-text="formatDate(m.created_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="totalPages > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Hal <span x-text="currentPage"></span> /
                    <span x-text="totalPages"></span>
                </p>
                <div class="flex gap-2">
                    <button @click="goToPage(currentPage-1)" :disabled="currentPage <= 1"
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40">
                        ←
                    </button>
                    <button @click="goToPage(currentPage+1)" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40">
                        →
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal detail manifest -->
        <div x-show="showDetail" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div @click.stop class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="'Detail: ' + (selectedManifest?.manifest_number || '')">
                    </h3>
                    <button @click="showDetail = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-5" x-show="selectedManifest">
                    <!-- Info header -->
                    <div class="grid grid-cols-2 gap-4 mb-5">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider">Event</p>
                            <p class="font-medium text-gray-800 mt-1" x-text="selectedManifest?.event?.name || '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider">Tujuan</p>
                            <p class="font-medium text-gray-800 mt-1" x-text="selectedManifest?.destination || '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider">Catatan</p>
                            <p class="text-gray-700 mt-1" x-text="selectedManifest?.notes || '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wider">Dibuat</p>
                            <p class="text-gray-700 mt-1" x-text="formatDate(selectedManifest?.created_at)"></p>
                        </div>
                    </div>
                    <!-- Daftar barang -->
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Item Manifest</p>
                    <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Barang</th>
                                <th class="px-4 py-2 text-center">Qty</th>
                                <th class="px-4 py-2 text-left">Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="mi in selectedManifest?.manifest_items || []" :key="mi.id">
                                <tr>
                                    <td class="px-4 py-2" x-text="mi.item?.name"></td>
                                    <td class="px-4 py-2 text-center" x-text="mi.qty_actual || mi.qty"></td>
                                    <td class="px-4 py-2">
                                        <span x-show="mi.condition === 'good'" class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-md text-xs font-medium">
                                            <i class="fas fa-check-circle"></i> Baik
                                        </span>
                                        <span x-show="mi.condition === 'broken'" class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-md text-xs font-medium">
                                            <i class="fas fa-wrench"></i> Rusak
                                            <span x-show="mi.incident_log" class="ml-1 text-[10px] bg-yellow-200 text-yellow-800 px-1 rounded">Insiden</span>
                                        </span>
                                        <span x-show="mi.condition === 'lost'" class="inline-flex items-center gap-1 bg-red-100 text-red-700 px-2 py-0.5 rounded-md text-xs font-medium">
                                            <i class="fas fa-exclamation-triangle"></i> Hilang
                                            <span x-show="mi.incident_log" class="ml-1 text-[10px] bg-red-200 text-red-800 px-1 rounded">Insiden</span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function manifestsPage() {
            return {
                items: [],
                loading: false,
                filterType: "",
                filterStatus: "",
                dateFrom: "",
                dateTo: "",
                currentPage: 1,
                totalPages: 1,
                showDetail: false,
                selectedManifest: null,

                async init() {
                    requireAuth();
                    await this.fetchData();
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const params = {
                            per_page: 15,
                            page: this.currentPage
                        };
                        if (this.filterType) params.type = this.filterType;
                        if (this.filterStatus) params.status = this.filterStatus;
                        if (this.dateFrom) params.date_from = this.dateFrom;
                        if (this.dateTo) params.date_to = this.dateTo;

                        const res = await apiRequest("GET", "/manifests", null, params);
                        this.items = res.data.data || res.data;
                        this.totalPages = res.data.last_page || 1;
                    } catch (e) {
                        toastError(parseError(e));
                    } finally {
                        this.loading = false;
                    }
                },

                async viewDetail(id) {
                    try {
                        showLoading("Memuat detail...");
                        const res = await apiRequest("GET", "/manifests/" + id);
                        this.selectedManifest = res.data;
                        this.showDetail = true;
                    } catch (e) {
                        toastError("Gagal memuat detail manifest");
                    } finally {
                        hideLoading();
                    }
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                        this.fetchData();
                    }
                },

                statusClass(s) {
                    return ({
                        draft: "bg-gray-100 text-gray-600",
                        in_progress: "bg-blue-100 text-blue-700",
                        completed: "bg-emerald-100 text-emerald-700",
                        has_issue: "bg-red-100 text-red-700",
                    } [s] || "bg-gray-100 text-gray-600");
                },

                statusLabel(s) {
                    return ({
                        draft: "Draft",
                        in_progress: "Berjalan",
                        completed: "Selesai",
                        has_issue: "Ada Masalah",
                    } [s] || s);
                },

                formatDate(d) {
                    if (!d) return "—";
                    return new Date(d).toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "short",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                },
            };
        }
    </script>
@endpush
