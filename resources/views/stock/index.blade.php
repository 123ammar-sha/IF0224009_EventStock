@extends('layouts.app')
@section('title', 'Kelola Stok')
@section('page-title', 'Kelola Stok')
@section('content')
<div x-data="stockPage()" x-init="init()" class="max-w-2xl">
    <!-- Tab switcher -->
    <div class="flex border-b border-gray-200 mb-6">
        <button
            @click="activeTab = 'add'"
            :class="activeTab === 'add'
                    ? 'border-b-2 border-indigo-600 text-indigo-600'
                    : 'text-gray-500 hover:text-gray-700'"
            class="px-5 py-3 text-sm font-medium transition-colors"
        >
            <i class="fas fa-plus mr-2"></i>
            Tambah Stok
        </button>
        <button
            @click="activeTab = 'adjust'"
            :class="activeTab === 'adjust'
                    ? 'border-b-2 border-indigo-600 text-indigo-600'
                    : 'text-gray-500 hover:text-gray-700'"
            class="px-5 py-3 text-sm font-medium transition-colors"
        >
            <i class="fas fa-sliders mr-2"></i>
            Adjustment Stok
        </button>
    </div>

    <!-- Tab: Tambah Stok -->
    <div x-show="activeTab === 'add'">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-sm text-gray-500 mb-5">
                Gunakan form ini untuk mencatat pembelian barang baru atau restock.
                Semua penambahan tercatat di riwayat transaksi.
            </p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                    <!-- Search Input -->
                    <div class="relative mb-2">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input
                            type="text"
                            x-model="itemSearch"
                            placeholder="Cari nama atau SKU barang..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/10"
                        />
                    </div>
                    <select
                        x-model="addForm.item_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Pilih barang...</option>
                        <template x-for="it in filteredItemsAdd()" :key="it.id">
                            <option :value="it.id" x-text="it.name + ' [SKU: ' + (it.sku || '-') + '] (stok: ' + it.available_qty + ')'"></option>
                        </template>
                    </select>
                    <p x-show="addErrors.item_id" x-text="addErrors.item_id" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah yang Ditambahkan</label>
                    <input
                        type="number"
                        x-model.number="addForm.quantity"
                        min="1"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p x-show="addErrors.quantity" x-text="addErrors.quantity" class="text-red-500 text-xs mt-1"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <input
                        type="text"
                        x-model="addForm.description"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: Pembelian bulan Juni 2026"
                    />
                    <p x-show="addErrors.description" x-text="addErrors.description" class="text-red-500 text-xs mt-1"></p>
                </div>

                <button
                    @click="submitAdd()"
                    :disabled="saving"
                    class="w-full flex items-center justify-center gap-2 py-2.5
                           bg-emerald-600 text-white rounded-lg font-medium
                           hover:bg-emerald-700 disabled:opacity-50"
                >
                    <i x-show="saving" class="fas fa-spinner animate-spin"></i>
                    <span x-text="saving ? 'Memproses...' : 'Tambah Stok'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tab: Adjustment Stok -->
    <div x-show="activeTab === 'adjust'">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg mb-5">
                <i class="fas fa-triangle-exclamation text-amber-600 mt-0.5"></i>
                <p class="text-sm text-amber-800">
                    Adjustment akan <strong>mengubah stok ke nilai yang ditentukan</strong>,
                    bukan menambahkan. Gunakan hanya untuk stock opname atau koreksi data.
                </p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barang</label>
                    <!-- Search Input -->
                    <div class="relative mb-2">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input
                            type="text"
                            x-model="itemSearchAdjust"
                            placeholder="Cari nama atau SKU barang..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/10"
                        />
                    </div>
                    <select
                        x-model="adjustForm.item_id"
                        @change="loadItemStock()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Pilih barang...</option>
                        <template x-for="it in filteredItemsAdjust()" :key="it.id">
                            <option :value="it.id" x-text="it.name + ' [SKU: ' + (it.sku || '-') + '] (stok: ' + it.available_qty + ')'"></option>
                        </template>
                    </select>
                </div>

                <div x-show="currentStock !== null" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Stok saat ini</p>
                        <p class="font-bold text-gray-900 text-xl" x-text="currentStock"></p>
                    </div>
                    <i class="fas fa-arrow-right text-gray-300"></i>
                    <div>
                        <p class="text-gray-400 text-xs">Setelah adjustment</p>
                        <p class="font-bold text-indigo-600 text-xl" x-text="adjustForm.new_available_qty || '?'"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Baru (setelah adjustment)</label>
                    <input
                        type="number"
                        x-model.number="adjustForm.new_available_qty"
                        min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                    <input
                        type="text"
                        x-model="adjustForm.reason"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: Stock opname Mei 2026"
                    />
                </div>

                <button
                    @click="submitAdjust()"
                    :disabled="saving"
                    class="w-full flex items-center justify-center gap-2 py-2.5
                           bg-amber-500 text-white rounded-lg font-medium
                           hover:bg-amber-600 disabled:opacity-50"
                >
                    <i x-show="saving" class="fas fa-spinner animate-spin"></i>
                    <span x-text="saving ? 'Memproses...' : 'Konfirmasi Adjustment'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function stockPage() {
        return {
            activeTab: "add",
            allItems: [],
            itemSearch: "",
            itemSearchAdjust: "",
            saving: false,
            currentStock: null,
            addForm: { item_id: "", quantity: 1, description: "" },
            addErrors: {},
            adjustForm: { item_id: "", new_available_qty: 0, reason: "" },
            adjustErrors: {},

            async init() {
                requireAuth();
                const res = await apiRequest("GET", "/items", null, { per_page: 1000 });
                this.allItems = res.data.data || res.data;
            },

            loadItemStock() {
                if (!this.adjustForm.item_id) {
                    this.currentStock = null;
                    return;
                }
                const item = this.allItems.find((i) => i.id == this.adjustForm.item_id);
                if (item) {
                    this.currentStock = item.available_qty;
                    this.adjustForm.new_available_qty = item.available_qty;
                }
            },

            async submitAdd() {
                if (!this.addForm.item_id) return toastError("Pilih barang");
                if (this.addForm.quantity < 1) return toastError("Jumlah minimal 1");

                this.saving = true;
                this.addErrors = {};
                try {
                    const res = await apiRequest("POST", "/stock/add", this.addForm);
                    toastSuccess(res.message || "Stok berhasil ditambahkan");
                    this.addForm = { item_id: "", quantity: 1, description: "" };
                    this.itemSearch = "";
                    const itemRes = await apiRequest("GET", "/items", null, { per_page: 1000 });
                    this.allItems = itemRes.data.data || itemRes.data;
                } catch (e) {
                    this.addErrors = parseValidationErrors(e);
                    toastError(parseError(e));
                } finally {
                    this.saving = false;
                }
            },

            filteredItemsAdd() {
                if (!this.itemSearch) return this.allItems;
                const q = this.itemSearch.toLowerCase();
                return this.allItems.filter(item => 
                    item.name.toLowerCase().includes(q) || 
                    (item.sku && item.sku.toLowerCase().includes(q))
                );
            },

            filteredItemsAdjust() {
                if (!this.itemSearchAdjust) return this.allItems;
                const q = this.itemSearchAdjust.toLowerCase();
                return this.allItems.filter(item => 
                    item.name.toLowerCase().includes(q) || 
                    (item.sku && item.sku.toLowerCase().includes(q))
                );
            },

            async submitAdjust() {
                if (!this.adjustForm.item_id) return toastError("Pilih barang");

                confirmAction(
                    async () => {
                        this.saving = true;
                        this.adjustErrors = {};
                        try {
                            const res = await apiRequest("POST", "/stock/adjust", this.adjustForm);
                            toastSuccess(res.message || "Stok berhasil diupdate");
                            this.currentStock = this.adjustForm.new_available_qty;
                            this.adjustForm = { item_id: "", new_available_qty: 0, reason: "" };
                            this.itemSearchAdjust = "";
                            this.currentStock = null;
                            const itemRes = await apiRequest("GET", "/items", null, { per_page: 1000 });
                            this.allItems = itemRes.data.data || itemRes.data;
                        } catch (e) {
                            this.adjustErrors = parseValidationErrors(e);
                            toastError(parseError(e));
                        } finally {
                            this.saving = false;
                        }
                    },
                    "Konfirmasi Adjustment",
                    `Stok akan diubah ke ${this.adjustForm.new_available_qty}. Lanjutkan?`,
                );
            },
        };
    }
</script>
@endpush
