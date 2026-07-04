@extends('layouts.app')
@section('title', 'Insiden')
@section('page-title', 'Log Insiden')
@section('content')
    <div x-data="incidentsPage()" x-init="init()">
        <!-- Filter (tetap sama) -->
        <div class="flex flex-wrap gap-3 mb-5">
            <select x-model="filterType" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                <option value="broken">Rusak</option>
                <option value="lost">Hilang</option>
            </select>
            <select x-model="filterResolved" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                <option value="false">Belum Selesai</option>
                <option value="true">Sudah Selesai</option>
            </select>
            <input type="date" x-model="dateFrom" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
            <input type="date" x-model="dateTo" @change="currentPage=1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm" />
        </div>

        <!-- Tabel insiden -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
            </div>
            <div x-show="!loading && incidents.length === 0" class="flex flex-col items-center py-20 text-gray-400">
                <i class="fas fa-shield-check text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Tidak ada insiden tercatat</p>
            </div>

            <div x-show="!loading && incidents.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Barang</th>
                            <th class="px-5 py-3 text-left">Tipe</th>
                            <th class="px-5 py-3 text-left">Catatan</th>
                            <th class="px-5 py-3 text-center">Qty Terdampak</th>
                            <th class="px-5 py-3 text-center">Qty Selesai</th>
                            <th class="px-5 py-3 text-center">Qty Gagal</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="inc in incidents" :key="inc.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3.5 font-medium text-gray-900">
                                    <!-- ✅ PERBAIKAN: Akses item dari manifest_item -->
                                    <span x-text="inc.manifest_item?.item?.name || inc.item?.name || '—'"></span>
                                    <span x-show="!inc.manifest_item?.item?.name" class="text-xs text-gray-400 ml-1">
                                        (ID: <span x-text="inc.manifest_item?.item_id || inc.item_id"></span>)
                                    </span>
                                    <!-- Tampilkan SKU jika ada -->
                                    <span x-show="inc.manifest_item?.item?.sku" class="text-xs text-gray-400 ml-2">
                                        <span x-text="inc.manifest_item?.item?.sku"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        :class="inc.type === 'broken' ? 'bg-yellow-100 text-yellow-700' :
                                            'bg-red-100 text-red-700'"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-text="inc.type === 'broken' ? 'Rusak' : 'Hilang'">
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate"
                                    x-text="inc.manifest_item?.notes || inc.notes || '—'">
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium" x-text="inc.qty_affected"></td>
                                <td class="px-5 py-3.5 text-center text-emerald-600 font-medium" x-text="inc.qty_resolved || 0"></td>
                                <td class="px-5 py-3.5 text-center text-red-600 font-medium" x-text="inc.qty_unresolved || 0"></td>
                                <td class="px-5 py-3.5">
                                    <span
                                        :class="inc.resolved ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                        class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-text="inc.resolved ? 'Selesai' : 'Belum Selesai'">
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-400 text-xs" x-text="formatDate(inc.created_at)">
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button x-show="!inc.resolved" @click="resolve(inc)"
                                        class="text-emerald-600 hover:text-emerald-800 text-xs px-3 py-1 border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                                        <i class="fas fa-check mr-1"></i> Tandai Selesai
                                    </button>
                                    <span x-show="inc.resolved" class="text-gray-300 text-xs">
                                        Sudah ditangani
                                    </span>
                                </td>
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
                    <button @click="goToPage(currentPage-1)" :disabled="currentPage <= 1"
                        class="px-3 py-1 border rounded text-sm disabled:opacity-40">←</button>
                    <button @click="goToPage(currentPage+1)" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border rounded text-sm disabled:opacity-40">→</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function incidentsPage() {
            return {
                incidents: [],
                loading: false,
                filterType: "",
                filterResolved: "",
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
                        const params = {
                            per_page: 15,
                            page: this.currentPage
                        };
                        if (this.filterType) params.type = this.filterType;
                        if (this.filterResolved !== "")
                            params.resolved = this.filterResolved;
                        if (this.dateFrom) params.date_from = this.dateFrom;
                        if (this.dateTo) params.date_to = this.dateTo;

                        const res = await apiRequest(
                            "GET",
                            "/incidents",
                            null,
                            params,
                        );
                        this.incidents = res.data.data || res.data;
                        this.totalPages = res.data.last_page || 1;
                    } catch (e) {
                        toastError(parseError(e));
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

                async resolve(inc) {
                    const itemName = this.getItemName(inc);
                    const { value: formValues } = await Swal.fire({
                        title: "Resolusi Insiden",
                        html: 
                            '<div class="text-left text-sm text-gray-600 mb-4">' +
                                '<p>Barang: <strong>' + itemName + '</strong></p>' +
                                '<p>Total Terdampak: <strong>' + inc.qty_affected + '</strong></p>' +
                            '</div>' +
                            '<div class="flex flex-col gap-3 text-left">' +
                                '<div>' +
                                    '<label class="block text-sm font-medium">Qty Selesai (Diperbaiki / Ditemukan)</label>' +
                                    '<input id="swal-input1" type="number" class="w-full border rounded px-3 py-2 mt-1" value="' + inc.qty_affected + '" min="0">' +
                                '</div>' +
                                '<div>' +
                                    '<label class="block text-sm font-medium">Qty Gagal (Rusak/Hilang Permanen)</label>' +
                                    '<input id="swal-input2" type="number" class="w-full border rounded px-3 py-2 mt-1" value="0" min="0">' +
                                '</div>' +
                            '</div>',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const q1 = parseInt(document.getElementById("swal-input1").value) || 0;
                            const q2 = parseInt(document.getElementById("swal-input2").value) || 0;
                            if (q1 + q2 !== inc.qty_affected) {
                                Swal.showValidationMessage('Total Qty Selesai + Qty Gagal harus sama dengan ' + inc.qty_affected);
                                return false;
                            }
                            return [q1, q2];
                        }
                    });

                    if (formValues) {
                        const [qty_resolved, qty_unresolved] = formValues;
                        try {
                            showLoading("Memproses...");
                            const res = await apiRequest("PATCH", "/incidents/" + inc.id + "/resolve", {
                                qty_resolved: qty_resolved,
                                qty_unresolved: qty_unresolved
                            });
                            
                            toastSuccess(res.message || "Insiden berhasil ditandai selesai");
                            await this.fetchData();
                        } catch (e) {
                            console.error('Error resolve:', e);
                            toastError(parseError(e));
                        } finally {
                            hideLoading();
                        }
                    }
                },
                getItemName(inc) {
                    return inc.manifest_item?.item?.name ||
                        inc.item?.name ||
                        'Item #' + (inc.manifest_item?.item_id || inc.item_id);
                },

                formatDate(d) {
                    if (!d) return "—";
                    return new Date(d).toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "short",
                        year: "numeric",
                    });
                },
            };
        }
    </script>
@endpush
