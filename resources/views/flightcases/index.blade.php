@extends('layouts.app')
@section('title', 'Flightcase')
@section('page-title', 'Manajemen Flightcase')
@section('content')
    <div x-data="flightcasesPage()" x-init="init()">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model="search" @input="onSearch()" placeholder="Cari flightcase..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <button @click="openCreateModal()"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                   rounded-lg text-sm hover:bg-indigo-700 whitespace-nowrap ml-3">
                <i class="fas fa-plus text-xs"></i>
                Tambah Flightcase
            </button>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
            </div>

            <div x-show="!loading && items.length === 0"
                class="flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="fas fa-briefcase text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Belum ada flightcase</p>
                <button @click="openCreateModal()" class="mt-3 text-sm text-indigo-600 hover:underline">
                    Tambah yang pertama
                </button>
            </div>

            <div x-show="!loading && items.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Kode</th>
                            <th class="px-5 py-3 text-left">Nama</th>
                            <th class="px-5 py-3 text-left">Deskripsi</th>
                            <th class="px-5 py-3 text-center">Jumlah Item</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-medium text-indigo-600" x-text="item.code"></td>
                                <td class="px-5 py-3.5 font-medium text-gray-900" x-text="item.name"></td>
                                <td class="px-5 py-3.5 text-gray-500 max-w-xs truncate" x-text="item.description || '—'">
                                </td>
                                <td class="px-5 py-3.5 text-center text-gray-600"
                                    x-text="item.items?.length || item.items_count || 0"></td>
                                <td class="px-5 py-3.5 text-right">
                                    <button @click="viewDetail(item)" class="text-sky-600 hover:text-sky-800 mr-2 p-1"
                                        title="Lihat isi">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button @click="edit(item)" class="text-indigo-600 hover:text-indigo-800 mr-2 p-1">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    <button @click="remove(item.id)" class="text-red-500 hover:text-red-700 p-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="totalPages > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>
                </p>
                <div class="flex gap-2">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40 hover:bg-gray-50">←</button>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40 hover:bg-gray-50">→</button>
                </div>
            </div>
        </div>

        <!-- Modal tambah/edit -->
        <div x-show="showModal" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div @click.stop class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900"
                        x-text="editMode ? 'Edit Flightcase' : 'Tambah Flightcase Baru'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <!-- Kode -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Flightcase</label>
                            <input type="text" x-model="formData.code"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Contoh: FC-001" />
                            <p x-show="formErrors.code" x-text="formErrors.code" class="text-red-500 text-xs mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <input type="text" x-model="formData.name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Contoh: Sound Package A" />
                            <p x-show="formErrors.name" x-text="formErrors.name" class="text-red-500 text-xs mt-1"></p>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea x-model="formData.description" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Deskripsi singkat..."></textarea>
                    </div>

                    <!-- Item bundling -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item dalam Flightcase</label>
                        <div class="flex gap-3 mb-3">
                            <select x-model="selectedItemId"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">Pilih barang...</option>
                                <template x-for="it in allItems" :key="it.id">
                                    <option :value="it.id" x-text="it.name"></option>
                                </template>
                            </select>
                            <input type="number" x-model.number="selectedQty" min="1" placeholder="Qty"
                                class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <button @click="addBundleItem()"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
                                Tambah
                            </button>
                        </div>

                        <div x-show="formData.items && formData.items.length > 0" class="space-y-2">
                            <template x-for="(bi, idx) in formData.items" :key="idx">
                                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-2.5">
                                    <div>
                                        <span class="font-medium text-gray-800 text-sm"
                                            x-text="getItemName(bi.item_id)"></span>
                                        <span class="text-gray-400 text-xs ml-2"
                                            x-text="'× ' + bi.quantity + ' unit'"></span>
                                    </div>
                                    <button @click="removeBundleItem(idx)"
                                        class="text-red-400 hover:text-red-600 text-sm">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <p x-show="formData.items && formData.items.length === 0" class="text-xs text-gray-400">
                            Belum ada item ditambahkan ke flightcase ini.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                    <button @click="closeModal()" class="px-4 py-2 text-sm text-gray-600">Batal</button>
                    <button @click="save()" :disabled="saving"
                        class="flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                           rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                        <i x-show="saving" class="fas fa-spinner animate-spin text-xs"></i>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal detail isi flightcase -->
        <div x-show="showDetailModal" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div @click.stop class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900"
                        x-text="'Isi: ' + (detailItem?.code || '') + ' — ' + (detailItem?.name || '')"></h3>
                    <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <p x-show="detailItem?.description" class="text-sm text-gray-500 mb-4"
                        x-text="detailItem?.description"></p>
                    <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 text-xs text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">Barang</th>
                                <th class="px-4 py-2 text-center">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="bi in detailItem?.items || []" :key="bi.id || bi.item_id">
                                <tr>
                                    <td class="px-4 py-2" x-text="bi.item?.name || bi.name || 'Item #' + bi.item_id"></td>
                                    <td class="px-4 py-2 text-center font-medium"
                                        x-text="bi.quantity || bi.pivot?.quantity || '—'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p x-show="!detailItem?.items || detailItem?.items.length === 0"
                        class="text-sm text-gray-400 text-center py-6">
                        Flightcase ini kosong.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function flightcasesPage() {
            return {
                items: [],
                allItems: [],
                loading: false,
                saving: false,
                showModal: false,
                showDetailModal: false,
                editMode: false,
                formData: {},
                formErrors: {},
                detailItem: {},
                search: "",
                currentPage: 1,
                totalPages: 1,
                selectedItemId: "",
                selectedQty: 1,

                async init() {
                    requireAuth();
                    await Promise.all([this.fetchData(), this.fetchAllItems()]);
                },

                async fetchAllItems() {
                    try {
                        const res = await apiRequest("GET", "/items", null, {
                            per_page: 200
                        });
                        this.allItems = res.data.data || res.data;
                    } catch (e) {
                        console.error('Error fetching items:', e);
                    }
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const params = {
                            per_page: 10,
                            page: this.currentPage
                        };
                        if (this.search) params.search = this.search;

                        const res = await apiRequest("GET", "/flightcases", null, params);
                        console.log('Flightcases response:', res); // Debug

                        // Menangkap data dari response
                        this.items = res.data || [];
                        this.totalPages = res.last_page || 1;

                        console.log('Items loaded:', this.items); // Debug
                    } catch (e) {
                        console.error('Error fetching flightcases:', e);
                        toastError("Gagal memuat data flightcase");
                    } finally {
                        this.loading = false;
                    }
                },

                openCreateModal() {
                    this.editMode = false;
                    this.formData = {
                        code: '',
                        name: '',
                        description: '',
                        items: []
                    };
                    this.formErrors = {};
                    this.selectedItemId = "";
                    this.selectedQty = 1;
                    this.showModal = true;
                },

                async edit(item) {
                    this.editMode = true;
                    try {
                        showLoading("Memuat detail...");
                        const res = await apiRequest("GET", "/flightcases/" + item.id);
                        console.log('Detail response:', res); // Debug

                        // Menangkap data dari response
                        const fc = res.data || res;

                        this.formData = {
                            id: fc.id,
                            code: fc.code,
                            name: fc.name,
                            description: fc.description || '',
                            items: (fc.items || []).map(bi => ({
                                item_id: bi.item_id || bi.id,
                                quantity: bi.quantity || bi.pivot?.qty || 1,
                            })),
                        };

                        console.log('Form data loaded:', this.formData); // Debug
                    } catch (e) {
                        console.error('Error loading detail:', e);
                        toastError('Gagal memuat detail');
                        // Fallback: gunakan data dari tabel
                        this.formData = {
                            id: item.id,
                            code: item.code,
                            name: item.name,
                            description: item.description || '',
                            items: [],
                        };
                    } finally {
                        hideLoading();
                    }
                    this.formErrors = {};
                    this.selectedItemId = "";
                    this.selectedQty = 1;
                    this.showModal = true;
                },

                async viewDetail(item) {
                    try {
                        showLoading("Memuat isi...");
                        const res = await apiRequest("GET", "/flightcases/" + item.id);
                        console.log('Detail view response:', res); // Debug

                        // Menangkap data dari response
                        this.detailItem = res.data || res;
                        this.showDetailModal = true;
                    } catch (e) {
                        console.error('Error loading detail:', e);
                        toastError("Gagal memuat detail flightcase");
                    } finally {
                        hideLoading();
                    }
                },

                closeModal() {
                    this.showModal = false;
                    this.formData = {};
                    this.formErrors = {};
                },

                addBundleItem() {
                    if (!this.selectedItemId) {
                        toastWarning("Pilih barang terlebih dahulu");
                        return;
                    }
                    if (this.selectedQty < 1) {
                        toastWarning("Qty minimal 1");
                        return;
                    }

                    if (!this.formData.items) this.formData.items = [];

                    // Cek apakah item sudah ada
                    const existing = this.formData.items.findIndex(i => i.item_id == this.selectedItemId);
                    if (existing >= 0) {
                        this.formData.items[existing].quantity += this.selectedQty;
                        toastSuccess("Jumlah item diperbarui");
                    } else {
                        this.formData.items.push({
                            item_id: parseInt(this.selectedItemId),
                            quantity: this.selectedQty,
                        });
                        toastSuccess("Item berhasil ditambahkan");
                    }

                    this.selectedItemId = "";
                    this.selectedQty = 1;
                },

                removeBundleItem(idx) {
                    this.formData.items.splice(idx, 1);
                },

                getItemName(id) {
                    const item = this.allItems.find(i => i.id === id);
                    return item ? item.name : "Item #" + id;
                },

                async save() {
                    this.saving = true;
                    this.formErrors = {};
                    try {
                        // Validasi minimal
                        if (!this.formData.code) {
                            this.formErrors.code = 'Kode flightcase wajib diisi';
                            this.saving = false;
                            return;
                        }
                        if (!this.formData.name) {
                            this.formErrors.name = 'Nama flightcase wajib diisi';
                            this.saving = false;
                            return;
                        }

                        let response;
                        if (this.editMode) {
                            response = await apiRequest("PUT", "/flightcases/" + this.formData.id, this.formData);
                            toastSuccess("Flightcase berhasil diupdate");
                        } else {
                            response = await apiRequest("POST", "/flightcases", this.formData);
                            toastSuccess("Flightcase berhasil ditambahkan");
                        }

                        console.log('Save response:', response); // Debug
                        this.closeModal();
                        await this.fetchData();
                    } catch (e) {
                        console.error('Save error:', e);
                        this.formErrors = parseValidationErrors(e);
                        if (Object.keys(this.formErrors).length === 0) {
                            toastError(parseError(e));
                        }
                    } finally {
                        this.saving = false;
                    }
                },

                remove(id) {
                    confirmAction(async () => {
                        try {
                            await apiRequest("DELETE", "/flightcases/" + id);
                            toastSuccess("Flightcase berhasil dihapus");
                            await this.fetchData();
                        } catch (e) {
                            console.error('Delete error:', e);
                            toastError(parseError(e));
                        }
                    }, "Hapus flightcase ini?", "Item bundling di dalamnya akan dihapus.");
                },

                onSearch() {
                    clearTimeout(this._t);
                    this._t = setTimeout(() => {
                        this.currentPage = 1;
                        this.fetchData();
                    }, 400);
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                        this.fetchData();
                    }
                },
            };
        }
    </script>
@endpush
