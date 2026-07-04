@extends('layouts.app')
@section('title', 'Barang Keluar')
@section('page-title', 'Manifest Barang Keluar')
@section('content')
<div x-data="outboundPage()" x-init="init()" class="max-w-5xl">
    <div class="bg-white rounded-2xl border border-slate-100 p-6 mb-6 shadow-modern-sm">
        <h2 class="font-bold text-slate-800 text-base mb-5 flex items-center gap-2">
            <i class="fas fa-truck text-indigo-500 text-sm"></i> Informasi Pengiriman
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Event -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Acara / Event</label>
                <select
                    x-model="form.event_id"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-slate-50/50"
                >
                    <option value="">Pilih acara...</option>
                    <template x-for="ev in events" :key="ev.id">
                        <option :value="ev.id" x-text="ev.name"></option>
                    </template>
                </select>
                <p x-show="errors.event_id" x-text="errors.event_id" class="text-red-500 text-xs mt-1"></p>
            </div>

            <!-- Tujuan -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tujuan Pengiriman</label>
                <input
                    type="text"
                    x-model="form.destination"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-slate-50/50"
                    placeholder="Nama venue / lokasi event"
                />
                <p x-show="errors.destination" x-text="errors.destination" class="text-red-500 text-xs mt-1"></p>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Catatan Logistik</label>
                <input
                    type="text"
                    x-model="form.notes"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-slate-50/50"
                    placeholder="Keterangan tambahan (opsional)"
                />
            </div>
        </div>
    </div>

    <!-- Tambah item satuan -->
    <div class="bg-white rounded-2xl border border-slate-100 p-6 mb-6 shadow-modern-sm">
        <h2 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
            <i class="fas fa-boxes-stacked text-indigo-500 text-sm"></i> Tambah Item Barang
        </h2>

        <!-- Search and Selection Grid -->
        <div class="grid grid-cols-1 gap-4 mb-5">
            <!-- Filter Input -->
            <div class="relative w-full">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input
                    type="text"
                    x-model="itemSearch"
                    placeholder="Ketik nama atau SKU barang untuk menyaring list di bawah..."
                    class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/30"
                />
            </div>
            
            <div class="flex flex-col gap-3 sm:flex-row">
                <!-- Dropdown Selector -->
                <select
                    x-model="selectedItemId"
                    class="flex-1 border border-slate-200 rounded-xl px-3 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50"
                >
                    <option value="">Pilih barang...</option>
                    <template x-for="it in filteredItems()" :key="it.id">
                        <option :value="it.id" x-text="it.name + ' [SKU: ' + (it.sku || '-') + '] (tersedia: ' + it.available_qty + ')'"></option>
                    </template>
                </select>
                
                <!-- Qty & Add Button -->
                <div class="flex gap-2">
                    <input
                        type="number"
                        x-model.number="selectedQty"
                        min="1"
                        placeholder="Qty"
                        class="w-20 border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-center
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50"
                    />
                    <button
                        @click="addItem()"
                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm hover:bg-indigo-700 transition-all font-semibold flex items-center gap-1.5 shrink-0 shadow-md shadow-indigo-600/10"
                    >
                        <i class="fas fa-plus text-xs"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        <!-- Daftar item yang sudah dipilih -->
        <div x-show="form.items.length > 0" class="border-t border-slate-50 pt-4">
            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest mb-3">Item yang telah ditambahkan</p>
            <div class="space-y-2.5 max-h-[350px] overflow-y-auto pr-1">
                <template x-for="(it, idx) in form.items" :key="idx">
                    <div class="flex items-center justify-between bg-slate-50/50 border border-slate-100 rounded-2xl px-4 py-3 hover:bg-slate-50 transition-colors">
                        <div class="flex-1 min-w-0 pr-4">
                            <span class="font-semibold text-slate-800 text-sm block truncate" x-text="getItemName(it.item_id)"></span>
                            <span class="text-slate-400 text-[10px] font-mono block mt-0.5" x-text="'SKU: ' + getItemSku(it.item_id) + ' • Stok Tersedia: ' + getItemStock(it.item_id)"></span>
                        </div>
                        <div class="flex items-center gap-4">
                            <!-- Qty controller langsung -->
                            <div class="flex items-center bg-white border border-slate-200 rounded-lg overflow-hidden p-0.5 shadow-sm">
                                <button type="button" @click="decreaseQty(idx)" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors text-xs font-bold rounded">-</button>
                                <input type="number" 
                                       x-model.number="it.qty" 
                                       @input="validateItemQty(idx)"
                                       min="1" 
                                       class="w-10 text-center text-xs font-bold text-slate-700 focus:outline-none border-none p-0" />
                                <button type="button" @click="increaseQty(idx)" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors text-xs font-bold rounded">+</button>
                            </div>
                            
                            <!-- Remove -->
                            <button @click="removeItem(idx)" class="w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-500 hover:text-rose-700 flex items-center justify-center transition-colors text-xs" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <p x-show="errors.items" x-text="errors.items" class="text-red-500 text-xs mt-2"></p>
    </div>

    <!-- Submit -->
    <div class="flex items-center justify-end gap-3">
        <a
            href="/manifests"
            class="px-5 py-2.5 border border-slate-200 rounded-xl text-sm font-semibold text-slate-500 hover:bg-slate-50 transition-colors"
        >
            Batal
        </a>
        <button
            @click="submit()"
            :disabled="submitting"
            class="flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white
                   rounded-xl text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition-all shadow-md shadow-blue-600/15"
        >
            <i x-show="submitting" class="fas fa-spinner animate-spin"></i>
            <i x-show="!submitting" class="fas fa-arrow-right-from-bracket"></i>
            <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Keluar'"></span>
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function outboundPage() {
        return {
            events: [],
            availableItems: [],
            itemSearch: "",
            form: {
                event_id: "",
                destination: "",
                notes: "",
                items: [],
            },
            errors: {},
            submitting: false,
            selectedItemId: "",
            selectedQty: 1,

            async init() {
                requireAuth();
                await Promise.all([
                    this.loadEvents(),
                    this.loadItems(),
                ]);
            },

            async loadEvents() {
                const res = await apiRequest("GET", "/events", null, { per_page: 100, status: 'upcoming,ongoing' });
                this.events = res.data.data || res.data;
            },

            async loadItems() {
                const res = await apiRequest("GET", "/items", null, { for_picker: true, per_page: 500 });
                this.availableItems = res.data.data || res.data;
            },

            filteredItems() {
                if (!this.itemSearch) return this.availableItems;
                const searchLower = this.itemSearch.toLowerCase();
                return this.availableItems.filter(item => 
                    item.name.toLowerCase().includes(searchLower) || 
                    (item.sku && item.sku.toLowerCase().includes(searchLower))
                );
            },

            addItem() {
                if (!this.selectedItemId) return toastWarning("Pilih barang terlebih dahulu");
                if (this.selectedQty < 1) return toastWarning("Qty minimal 1");

                const item = this.availableItems.find((i) => i.id == this.selectedItemId);
                if (item && this.selectedQty > item.available_qty) {
                    return toastError(`Stok ${item.name} hanya ${item.available_qty}`);
                }

                const existing = this.form.items.findIndex((i) => i.item_id == this.selectedItemId);
                if (existing >= 0) {
                    const newQty = this.form.items[existing].qty + this.selectedQty;
                    if (item && newQty > item.available_qty) {
                        return toastError(`Stok ${item.name} tidak mencukupi (Maksimal: ${item.available_qty})`);
                    }
                    this.form.items[existing].qty = newQty;
                } else {
                    this.form.items.push({
                        item_id: parseInt(this.selectedItemId),
                        qty: this.selectedQty,
                    });
                }

                this.selectedItemId = "";
                this.selectedQty = 1;
                this.itemSearch = "";
            },

            removeItem(idx) {
                this.form.items.splice(idx, 1);
            },

            getItemName(id) {
                return this.availableItems.find((i) => i.id === id)?.name || "Item #" + id;
            },

            getItemSku(id) {
                return this.availableItems.find((i) => i.id === id)?.sku || "—";
            },

            getItemStock(id) {
                return this.availableItems.find((i) => i.id === id)?.available_qty ?? 0;
            },

            validateItemQty(idx) {
                const it = this.form.items[idx];
                if (!it) return;
                if (it.qty < 1 || isNaN(it.qty)) it.qty = 1;
                
                const item = this.availableItems.find((i) => i.id == it.item_id);
                if (item && it.qty > item.available_qty) {
                    it.qty = item.available_qty;
                    toastWarning(`Stok ${item.name} maksimal ${item.available_qty}`);
                }
            },

            decreaseQty(idx) {
                if (this.form.items[idx].qty > 1) {
                    this.form.items[idx].qty--;
                    this.validateItemQty(idx);
                }
            },

            increaseQty(idx) {
                this.form.items[idx].qty++;
                this.validateItemQty(idx);
            },

            async submit() {
                if (!this.form.event_id) return toastError("Pilih acara terlebih dahulu");
                if (this.form.items.length === 0) {
                    return toastError("Tambahkan minimal 1 item");
                }

                this.submitting = true;
                this.errors = {};
                try {
                    await apiRequest("POST", "/manifests/outbound", this.form);
                    toastSuccess("Manifest keluar berhasil dibuat");
                    setTimeout(() => { window.location.href = "/manifests"; }, 1500);
                } catch (e) {
                    this.errors = parseValidationErrors(e);
                    toastError(parseError(e));
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
@endpush
