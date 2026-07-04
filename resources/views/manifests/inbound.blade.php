@extends('layouts.app')
@section('title', 'Barang Kembali')
@section('page-title', 'Manifest Barang Kembali')
@section('content')
    <div x-data="inboundPage()" x-init="init()" class="max-w-3xl">
        <!-- Pilih manifest outbound referensi -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="font-semibold text-gray-900 mb-4">Manifest Referensi</h2>
            <p class="text-sm text-gray-500 mb-4">
                Pilih manifest keluar yang menjadi referensi pengembalian barang.
            </p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Manifest Outbound</label>
                <select x-model="form.outbound_manifest_id" @change="loadOutboundItems()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Pilih manifest keluar...</option>
                    <template x-for="m in outboundManifests" :key="m.id">
                        <option :value="m.id"
                            x-text="m.manifest_number + ' — ' + (m.event?.name || 'No event') + ' (' + m.destination + ')'">
                        </option>
                    </template>
                </select>
                <p x-show="errors.outbound_manifest_id" x-text="errors.outbound_manifest_id"
                    class="text-red-500 text-xs mt-1"></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan Pengembalian</label>
                    <input type="text" x-model="form.destination"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: Gudang Pusat" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <input type="text" x-model="form.notes"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Keterangan tambahan (opsional)" />
                </div>
            </div>
        </div>

        <!-- Daftar item untuk dikembalikan -->
        <div x-show="form.items.length > 0" class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <h2 class="font-semibold text-gray-900 mb-4">Kondisi Pengembalian</h2>
            <p class="text-sm text-gray-500 mb-4">
                Isi kondisi setiap barang yang dikembalikan. Jika rusak atau hilang, sertakan catatan.
            </p>

            <div class="space-y-4">
                <template x-for="(item, idx) in form.items" :key="idx">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
                            <div>
                                <span class="font-medium text-gray-900" x-text="item._name"></span>
                                <span class="text-xs text-gray-400 ml-2"
                                    x-text="'(qty asal: ' + item._original_qty + ')'"></span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="splitItem(idx)" 
                                    class="text-[11px] text-blue-600 border border-blue-200 bg-blue-50 hover:bg-blue-100 px-2 py-1 rounded transition-colors"
                                    title="Pecah menjadi kondisi berbeda (misal: 1 rusak, 1 baik)">
                                    <i class="fas fa-code-branch"></i> Pisah Kondisi
                                </button>
                                <button type="button" @click="removeItem(idx)" x-show="canRemove(item.item_id)"
                                    class="text-[11px] text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition-colors"
                                    title="Hapus baris ini">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <!-- Qty actual -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty Kembali</label>
                                <input type="number" x-model.number="item.qty_actual" :max="item._original_qty"
                                    min="0"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>

                            <!-- Kondisi -->
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Kondisi</label>
                                <select x-model="item.condition"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="good">Baik</option>
                                    <option value="broken">Rusak</option>
                                    <option value="lost">Hilang</option>
                                </select>
                            </div>

                            <!-- Notes (tampil jika broken/lost) -->
                            <div x-show="item.condition === 'broken' || item.condition === 'lost'">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                                <input type="text" x-model="item.notes"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Jelaskan kondisi..." />
                            </div>
                        </div>

                        <!-- Status indicator -->
                        <div class="mt-2">
                            <span x-show="item.condition === 'good'" class="text-xs text-emerald-600">
                                <i class="fas fa-check-circle mr-1"></i> Stok akan kembali normal
                            </span>
                            <span x-show="item.condition === 'broken'" class="text-xs text-yellow-600">
                                <i class="fas fa-wrench mr-1"></i> Item akan masuk status maintenance, insiden tercatat
                            </span>
                            <span x-show="item.condition === 'lost'" class="text-xs text-red-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Total stok dikurangi, status hilang,
                                insiden tercatat
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            <p x-show="errors.items" x-text="errors.items" class="text-red-500 text-xs mt-2"></p>
        </div>

        <!-- Empty state ketika belum pilih manifest -->
        <div x-show="form.items.length === 0 && form.outbound_manifest_id"
            class="bg-white rounded-xl border border-gray-200 p-6 mb-5">
            <div class="flex items-center justify-center py-10 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat item manifest...
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="/manifests"
                class="px-5 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                Batal
            </a>
            <button @click="submit()" :disabled="submitting"
                class="flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white
                   rounded-lg text-sm font-medium hover:bg-emerald-700 disabled:opacity-50">
                <i x-show="submitting" class="fas fa-spinner animate-spin"></i>
                <i x-show="!submitting" class="fas fa-arrow-right-to-bracket"></i>
                <span x-text="submitting ? 'Memproses...' : 'Konfirmasi Kembali'"></span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function inboundPage() {
            return {
                outboundManifests: [],
                form: {
                    outbound_manifest_id: "",
                    destination: "",
                    notes: "",
                    items: [],
                },
                errors: {},
                submitting: false,

                async init() {
                    requireAuth();
                    await this.loadOutboundManifests();
                },

                async loadOutboundManifests() {
                    try {
                        // Langsung filter di API
                        const res = await apiRequest("GET", "/manifests", null, {
                            type: "outbound",
                            status: "in_progress", // ← Filter langsung di backend
                            per_page: 100,
                        });

                        this.outboundManifests = res.data.data || res.data;

                        if (this.outboundManifests.length === 0) {
                            toastWarning('Tidak ada manifest outbound dengan status in_progress');
                        }
                    } catch (e) {
                        console.error('Error:', e);
                        toastError("Gagal memuat daftar manifest outbound");
                    }
                },
                async loadOutboundItems() {
                    if (!this.form.outbound_manifest_id) {
                        this.form.items = [];
                        return;
                    }

                    try {
                        showLoading("Memuat item...");
                        const res = await apiRequest("GET", "/manifests/" + this.form.outbound_manifest_id);
                        const manifest = res.data;

                        console.log('=== OUTBOUND MANIFEST DETAIL ===');
                        console.log('Manifest:', {
                            id: manifest.id,
                            number: manifest.manifest_number,
                            type: manifest.type,
                            status: manifest.status,
                        });

                        console.log('Manifest Items:', manifest.manifest_items);

                        // Cek field qty yang tersedia
                        if (manifest.manifest_items && manifest.manifest_items.length > 0) {
                            const sample = manifest.manifest_items[0];
                            console.log('Sample item fields:', {
                                qty: sample.qty,
                                qty_requested: sample.qty_requested,
                                qty_actual: sample.qty_actual,
                                'item?.qty': sample.item?.qty,
                            });
                        }

                        // Filter consumable
                        const filteredItems = (manifest.manifest_items || []).filter((mi) => {
                            return mi.item?.category?.type !== 'consumable';
                        });

                        const skippedCount = (manifest.manifest_items || []).length - filteredItems.length;
                        if (skippedCount > 0) {
                            toastWarning(
                                `${skippedCount} barang consumable (habis pakai) tidak dimasukkan ke form pengembalian.`
                            );
                        }

                        this.form.items = filteredItems.map((mi, index) => {
                            // PRIORITAS: qty_actual untuk outbound
                            const qty = mi.qty_actual ?? mi.qty ?? mi.qty_requested ?? 0;

                            console.log(`Item ${index + 1}:`, {
                                name: mi.item?.name,
                                qty_actual: mi.qty_actual,
                                qty_requested: mi.qty_requested,
                                qty: mi.qty,
                                selected_qty: qty,
                            });

                            return {
                                item_id: mi.item_id || mi.item?.id,
                                qty_actual: qty,
                                condition: "good",
                                notes: "",
                                _name: mi.item?.name || "Item #" + mi.item_id,
                                _original_qty: qty,
                            };
                        });

                        console.log('Final form items:', this.form.items);
                        console.log('=== END DEBUG ===');

                    } catch (e) {
                        console.error('Error loading items:', e);
                        toastError("Gagal memuat item manifest");
                    } finally {
                        hideLoading();
                    }
                },

                splitItem(idx) {
                    const item = this.form.items[idx];
                    if (item.qty_actual <= 1) {
                        toastWarning("Kuantitas (Qty) tidak cukup untuk dipisah lagi.");
                        return;
                    }
                    
                    // Pindahkan 1 qty ke baris baru
                    item.qty_actual -= 1;
                    
                    const newItem = {
                        item_id: item.item_id,
                        qty_actual: 1,
                        condition: "broken", // Default split to broken
                        notes: "",
                        _name: item._name,
                        _original_qty: item._original_qty,
                    };
                    
                    // Sisipkan setelah item saat ini
                    this.form.items.splice(idx + 1, 0, newItem);
                },

                canRemove(itemId) {
                    return this.form.items.filter(i => i.item_id === itemId).length > 1;
                },

                removeItem(idx) {
                    const item = this.form.items[idx];
                    this.form.items.splice(idx, 1);
                    // Kembalikan qty_actual ke baris pertama yang memiliki item_id sama
                    const firstMatch = this.form.items.find(i => i.item_id === item.item_id);
                    if (firstMatch) {
                        firstMatch.qty_actual += item.qty_actual;
                    }
                },

                async submit() {
                    if (!this.form.outbound_manifest_id) return toastError("Pilih manifest referensi");
                    if (this.form.items.length === 0) return toastError("Tidak ada item untuk dikembalikan");

                    // Bersihkan helper fields sebelum submit
                    const submitData = {
                        outbound_manifest_id: parseInt(this.form.outbound_manifest_id),
                        destination: this.form.destination,
                        notes: this.form.notes,
                        items: this.form.items.map((it) => {
                            const obj = {
                                item_id: it.item_id,
                                qty_actual: it.qty_actual,
                                condition: it.condition,
                            };
                            if (it.condition !== "good" && it.notes) {
                                obj.notes = it.notes;
                            }
                            return obj;
                        }),
                    };

                    this.submitting = true;
                    this.errors = {};
                    try {
                        await apiRequest("POST", "/manifests/inbound", submitData);
                        toastSuccess("Manifest kembali berhasil dibuat");
                        setTimeout(() => {
                            window.location.href = "/manifests";
                        }, 1500);
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
