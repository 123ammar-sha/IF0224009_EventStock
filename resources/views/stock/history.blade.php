@extends('layouts.app')
@section('title', 'Riwayat Stok')
@section('page-title', 'Riwayat Transaksi Stok')
@section('content')
<div x-data="stockHistoryPage()" x-init="init()">
    <!-- Filter -->
    <div class="flex flex-wrap gap-3 mb-5">
        <select
            x-model="filterType"
            @change="currentPage=1; fetchData()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
        >
            <option value="">Semua Tipe</option>
            <option value="in">Masuk</option>
            <option value="out">Keluar</option>
            <option value="adjustment">Adjustment</option>
            <option value="correction">Koreksi</option>
        </select>
        <input
            type="date"
            x-model="dateFrom"
            @change="currentPage=1; fetchData()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
        />
        <input
            type="date"
            x-model="dateTo"
            @change="currentPage=1; fetchData()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
        />
        <button
            @click="filterType=''; dateFrom=''; dateTo=''; currentPage=1; fetchData()"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
        >
            Reset filter
        </button>
    </div>

    <!-- Tabel riwayat -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
            <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
        </div>

        <div x-show="!loading && transactions.length === 0" class="flex flex-col items-center py-20 text-gray-400">
            <i class="fas fa-clock-rotate-left text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">Belum ada riwayat transaksi</p>
        </div>

        <div x-show="!loading && transactions.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Waktu</th>
                        <th class="px-5 py-3 text-left">Barang</th>
                        <th class="px-5 py-3 text-left">Tipe</th>
                        <th class="px-5 py-3 text-center">Perubahan</th>
                        <th class="px-5 py-3 text-center">Sebelum</th>
                        <th class="px-5 py-3 text-center">Sesudah</th>
                        <th class="px-5 py-3 text-left">Keterangan</th>
                        <th class="px-5 py-3 text-left">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="t in transactions" :key="t.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-400 text-xs" x-text="formatDate(t.created_at)"></td>
                            <td class="px-5 py-3 font-medium text-gray-900" x-text="t.item?.name || '—'"></td>
                            <td class="px-5 py-3">
                                <span class="status-badge" :class="typeClass(t.type)" x-text="typeLabel(t.type)"></span>
                            </td>
                            <td
                                class="px-5 py-3 text-center font-bold"
                                :class="t.qty_change > 0 ? 'text-emerald-600' : 'text-red-600'"
                            >
                                <span x-text="(t.qty_change > 0 ? '+' : '') + t.qty_change"></span>
                            </td>
                            <td class="px-5 py-3 text-center text-gray-500" x-text="t.qty_before"></td>
                            <td class="px-5 py-3 text-center text-gray-900 font-medium" x-text="t.qty_after"></td>
                            <td class="px-5 py-3 text-gray-500 max-w-xs truncate" x-text="t.description || '—'"></td>
                            <td class="px-5 py-3 text-gray-500" x-text="t.user?.name || '—'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div x-show="totalPages > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
            <p class="text-sm text-gray-500">
                Hal <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
            </p>
            <div class="flex gap-2">
                <button @click="goToPage(currentPage-1)" :disabled="currentPage<=1"
                    class="px-3 py-1 border rounded text-sm disabled:opacity-40">←</button>
                <button @click="goToPage(currentPage+1)" :disabled="currentPage>=totalPages"
                    class="px-3 py-1 border rounded text-sm disabled:opacity-40">→</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function stockHistoryPage() {
        return {
            transactions: [],
            loading: false,
            filterType: "",
            dateFrom: "",
            dateTo: "",
            currentPage: 1,
            totalPages: 1,

            async init() {
                requireAuth();
                await this.fetchData();
            },

            async fetchData() {
                this.loading = true;
                try {
                    const params = { per_page: 20, page: this.currentPage };
                    if (this.filterType) params.type = this.filterType;
                    if (this.dateFrom) params.date_from = this.dateFrom;
                    if (this.dateTo) params.date_to = this.dateTo;

                    const res = await apiRequest("GET", "/stock/history", null, params);
                    this.transactions = res.data.data || res.data;
                    this.totalPages = res.data.last_page || 1;
                } catch (e) {
                    toastError("Gagal memuat riwayat stok");
                } finally {
                    this.loading = false;
                }
            },

            goToPage(p) {
                if (p >= 1 && p <= this.totalPages) {
                    this.currentPage = p;
                    this.fetchData();
                }
            },

            typeClass(t) {
                return ({
                    in: "type-in",
                    out: "type-out",
                    adjustment: "type-adjustment",
                    correction: "type-correction",
                }[t] || "bg-gray-100 text-gray-600");
            },

            typeLabel(t) {
                return ({
                    in: "Masuk",
                    out: "Keluar",
                    adjustment: "Adjustment",
                    correction: "Koreksi",
                }[t] || t);
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
