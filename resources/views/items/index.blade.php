@extends('layouts.app')
@section('title', 'Barang')
@section('page-title', 'Manajemen Barang')
@section('content')
    <div x-data="itemsPage()" x-init="init()">
        <!-- Header aksi -->
        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <!-- Pencarian -->
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model="search" @input="onSearch()" placeholder="Cari nama atau SKU..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>

            <!-- Filter kategori -->
            <select x-model="filterCategory" @change="currentPage = 1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Kategori</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>

            <!-- Filter status -->
            <select x-model="filterStatus" @change="currentPage = 1; fetchData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="on_duty">Digunakan</option>
                <option value="maintenance">Perawatan</option>
                <option value="lost">Hilang</option>
            </select>

            <template x-if="!isCrewUser">
                <button @click="openCreateModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                           rounded-lg text-sm hover:bg-indigo-700 whitespace-nowrap">
                    <i class="fas fa-plus text-xs"></i>
                    Tambah Barang
                </button>
            </template>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
            </div>

            <div x-show="!loading && items.length === 0"
                class="flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Belum ada barang</p>
            </div>

            <div x-show="!loading && items.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Barang</th>
                            <th class="px-5 py-3 text-left">SKU</th>
                            <th class="px-5 py-3 text-left">Kategori</th>
                            <th class="px-5 py-3 text-center">Stok Tersedia</th>
                            <th class="px-5 py-3 text-center">Total Stok</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-gray-900" x-text="item.name"></div>
                                    <div class="text-xs text-gray-400 mt-0.5"
                                        x-text="item.category?.type === 'asset' ? 'Asset' : 'Consumable'"></div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-gray-600 text-xs" x-text="item.sku || '—'"></td>
                                <td class="px-5 py-3.5 text-gray-600" x-text="item.category?.name || '—'"></td>
                                <td class="px-5 py-3.5 text-center font-semibold text-gray-900" x-text="item.available_qty">
                                </td>
                                <td class="px-5 py-3.5 text-center text-gray-500" x-text="item.total_qty"></td>
                                <td class="px-5 py-3.5">
                                    <span class="status-badge" :class="statusClass(item.status)"
                                        x-text="statusLabel(item.status)">
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <template x-if="!isCrewUser">
                                        <span>
                                            <button @click="edit(item)"
                                                class="text-indigo-600 hover:text-indigo-800 mr-3 p-1">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            <button @click="remove(item.id)" class="text-red-500 hover:text-red-700 p-1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </span>
                                    </template>
                                    <template x-if="isCrewUser">
                                        <span class="text-xs text-gray-400 italic">Hanya lihat</span>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-show="totalPages > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Halaman <span x-text="currentPage"></span> dari
                    <span x-text="totalPages"></span>
                </p>
                <div class="flex gap-2">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                        class="px-3 py-1 border border-gray-300 rounded text-sm
                               disabled:opacity-40 hover:bg-gray-50">
                        ← Sebelumnya
                    </button>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border border-gray-300 rounded text-sm
                               disabled:opacity-40 hover:bg-gray-50">
                        Berikutnya →
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal tambah/edit -->
        <div x-show="showModal" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div @click.stop class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="editMode ? 'Edit Barang' : 'Tambah Barang Baru'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                        <input type="text" x-model="formData.name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contoh: Sound System 15 inch" />
                        <p x-show="formErrors.name" x-text="formErrors.name" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            SKU
                            <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <input type="text" x-model="formData.sku"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contoh: SS-15-001" />
                        <p x-show="formErrors.sku" x-text="formErrors.sku" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select x-model="formData.category_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Pilih kategori...</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        <p x-show="formErrors.category_id" x-text="formErrors.category_id"
                            class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- Stok awal (hanya saat tambah baru, bukan edit) -->
                    <div x-show="!editMode">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Stok Awal
                        </label>
                        <input type="number" x-model="formData.initial_qty" min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="0" />
                        <p class="text-xs text-gray-400 mt-1">
                            Untuk menambah stok setelah dibuat, gunakan menu Kelola
                            Stok
                        </p>
                        <p x-show="formErrors.initial_qty" x-text="formErrors.initial_qty"
                            class="text-red-500 text-xs mt-1"></p>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                    <button @click="closeModal()" class="px-4 py-2 text-sm text-gray-600">
                        Batal
                    </button>
                    <button @click="save()" :disabled="saving"
                        class="flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white
                               rounded-lg text-sm hover:bg-indigo-700 disabled:opacity-50">
                        <i x-show="saving" class="fas fa-spinner animate-spin text-xs"></i>
                        <span x-text="saving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function itemsPage() {
            return {
                items: [],
                categories: [],
                loading: false,
                saving: false,
                showModal: false,
                editMode: false,
                formData: {},
                formErrors: {},
                search: "",
                filterCategory: "",
                filterStatus: "",
                currentPage: 1,
                totalPages: 1,
                isCrewUser: false,

                async init() {
                    requireAuth();
                    this.isCrewUser = isCrew();
                    await Promise.all([this.fetchCategories(), this.fetchData()]);
                },

                async fetchCategories() {
                    try {
                        const res = await apiRequest("GET", "/categories", null, {
                            per_page: 100,
                        });
                        this.categories = res.data.data || res.data;
                    } catch (e) {}
                },

                async fetchData() {
                    this.loading = true;
                    try {
                        const params = {
                            per_page: 10,
                            page: this.currentPage,
                        };
                        if (this.search) params.search = this.search;
                        if (this.filterCategory) params.category_id = this.filterCategory;
                        if (this.filterStatus) params.status = this.filterStatus;

                        const res = await apiRequest("GET", "/items", null, params);

                        // Ambil data items
                        this.items = res.data || [];

                        // Ambil meta untuk pagination
                        if (res.meta) {
                            this.totalPages = res.meta.last_page || 1;
                            this.currentPage = res.meta.current_page || 1;
                        } else {
                            // Fallback jika meta tidak ada
                            this.totalPages = 1;
                        }
                    } catch (e) {
                        console.error('Error fetching items:', e);
                        toastError("Gagal memuat data barang");
                    } finally {
                        this.loading = false;
                    }
                },

                openCreateModal() {
                    this.editMode = false;
                    this.formData = {
                        initial_qty: 0
                    };
                    this.formErrors = {};
                    this.showModal = true;
                },

                edit(item) {
                    this.editMode = true;
                    this.formData = {
                        id: item.id,
                        name: item.name,
                        sku: item.sku,
                        category_id: item.category_id,
                    };
                    this.formErrors = {};
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.formData = {};
                    this.formErrors = {};
                },

                async save() {
                    this.saving = true;
                    this.formErrors = {};
                    try {
                        if (this.editMode) {
                            // EDIT MODE - tidak kirim total_qty
                            const updateData = {
                                name: this.formData.name,
                                sku: this.formData.sku || null,
                                category_id: this.formData.category_id,
                            };

                            await apiRequest(
                                "PUT",
                                "/items/" + this.formData.id,
                                updateData
                            );
                            toastSuccess("Barang berhasil diupdate");
                        } else {
                            // CREATE MODE - kirim total_qty dari initial_qty
                            const createData = {
                                name: this.formData.name,
                                sku: this.formData.sku || null,
                                category_id: this.formData.category_id,
                                initial_qty: parseInt(this.formData.initial_qty) || 0,
                                total_qty: parseInt(this.formData.initial_qty) || 0
                            };

                            await apiRequest("POST", "/items", createData);
                            toastSuccess("Barang berhasil ditambahkan");
                        }
                        this.closeModal();
                        await this.fetchData();
                    } catch (e) {
                        this.formErrors = parseValidationErrors(e);
                        if (Object.keys(this.formErrors).length === 0) {
                            toastError(parseError(e));
                        }
                    } finally {
                        this.saving = false;
                    }
                },

                remove(id) {
                    confirmAction(
                        async () => {
                                try {
                                    await apiRequest("DELETE", "/items/" + id);
                                    toastSuccess("Barang berhasil dihapus");
                                    await this.fetchData();
                                } catch (e) {
                                    toastError(parseError(e));
                                }
                            },
                            "Hapus barang ini?",
                            "Stok dan riwayat barang akan ikut terhapus.",
                    );
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

                statusClass(s) {
                    return ({
                        available: "status-available",
                        on_duty: "status-on-duty",
                        maintenance: "status-maintenance",
                        lost: "status-lost",
                    } [s] || "bg-gray-100 text-gray-600");
                },

                statusLabel(s) {
                    return ({
                        available: "Tersedia",
                        on_duty: "Digunakan",
                        maintenance: "Perawatan",
                        lost: "Hilang",
                    } [s] || s);
                },
            };
        }
    </script>
@endpush
