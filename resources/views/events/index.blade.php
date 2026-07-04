@extends('layouts.app')
@section('title', 'Events')
@section('page-title', 'Manajemen Acara')
@section('content')
    <div x-data="eventsPage()" x-init="init()">
        <!-- Header -->
        <div class="flex items-center justify-between mb-5">
            <div class="relative flex-1 max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" x-model="search" @input="onSearch()" placeholder="Cari acara..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <button @click="openCreateModal()"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                   rounded-lg text-sm hover:bg-indigo-700 whitespace-nowrap ml-3">
                <i class="fas fa-plus text-xs"></i>
                Tambah Acara
            </button>
        </div>

        <!-- Tabel -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
            </div>

            <div x-show="!loading && items.length === 0"
                class="flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="fas fa-calendar-days text-4xl mb-3 opacity-30"></i>
                <p class="text-sm">Belum ada acara</p>
                <button @click="openCreateModal()" class="mt-3 text-sm text-indigo-600 hover:underline">
                    Tambah yang pertama
                </button>
            </div>

            <div x-show="!loading && items.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Nama Acara</th>
                            <th class="px-5 py-3 text-left">Lokasi</th>
                            <th class="px-5 py-3 text-left">Mulai</th>
                            <th class="px-5 py-3 text-left">Selesai</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-gray-900" x-text="item.name"></div>
                                    <div class="text-xs text-gray-400 mt-0.5 max-w-xs truncate"
                                        x-text="item.description || ''"></div>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600" x-text="item.venue || '—'"></td>
                                <td class="px-5 py-3.5 text-gray-500 text-xs" x-text="formatDate(item.start_date)"></td>
                                <td class="px-5 py-3.5 text-gray-500 text-xs" x-text="formatDate(item.end_date)"></td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="eventStatusClass(item)" x-text="eventStatusLabel(item)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <button @click="edit(item)" class="text-indigo-600 hover:text-indigo-800 mr-3 p-1">
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
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40 hover:bg-gray-50">
                        ← Sebelumnya
                    </button>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                        class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-40 hover:bg-gray-50">
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
            <div @click.stop class="bg-white rounded-2xl w-full max-w-lg shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="editMode ? 'Edit Acara' : 'Tambah Acara Baru'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Acara</label>
                        <input type="text" x-model="formData.name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contoh: Konser Musik Jakarta 2026" />
                        <p x-show="formErrors.name" x-text="formErrors.name" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                        <input type="text" x-model="formData.venue"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Contoh: Istora Senayan, Jakarta" />
                        <p x-show="formErrors.venue" x-text="formErrors.venue" class="text-red-500 text-xs mt-1"></p>
                    </div>

                    <!-- Tanggal -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date" x-model="formData.start_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p x-show="formErrors.start_date" x-text="formErrors.start_date"
                                class="text-red-500 text-xs mt-1"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                            <input type="date" x-model="formData.end_date"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                            <p x-show="formErrors.end_date" x-text="formErrors.end_date"
                                class="text-red-500 text-xs mt-1"></p>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea x-model="formData.description" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Deskripsi singkat acara..."></textarea>
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
    </div>
@endsection

@push('scripts')
    <script>
        function eventsPage() {
            return {
                items: [],
                loading: false,
                saving: false,
                showModal: false,
                editMode: false,
                formData: {},
                formErrors: {},
                search: "",
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
                            per_page: 10,
                            page: this.currentPage
                        };
                        if (this.search) params.search = this.search;

                        const res = await apiRequest("GET", "/events", null, params);
                        this.items = res.data || [];
                        this.totalPages = res.last_page || 1;
                        this.currentPage = res.current_page || 1;
                    } catch (e) {
                        toastError("Gagal memuat data acara");
                    } finally {
                        this.loading = false;
                    }
                },

                openCreateModal() {
                    this.editMode = false;
                    this.formData = {};
                    this.formErrors = {};
                    this.showModal = true;
                },

                edit(item) {
                    this.editMode = true;
                    this.formData = {
                        id: item.id,
                        name: item.name,
                        venue: item.venue,
                        start_date: item.start_date,
                        end_date: item.end_date,
                        description: item.description,
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
                            await apiRequest("PUT", "/events/" + this.formData.id, this.formData);
                            toastSuccess("Acara berhasil diupdate");
                        } else {
                            await apiRequest("POST", "/events", this.formData);
                            toastSuccess("Acara berhasil ditambahkan");
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
                    confirmAction(async () => {
                        try {
                            await apiRequest("DELETE", "/events/" + id);
                            toastSuccess("Acara berhasil dihapus");
                            await this.fetchData();
                        } catch (e) {
                            toastError(parseError(e));
                        }
                    }, "Hapus acara ini?", "Manifest terkait tidak akan dihapus.");
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

                eventStatusClass(item) {
                    const now = new Date();
                    const start = item.start_date ? new Date(item.start_date) : null;
                    const end = item.end_date ? new Date(item.end_date) : null;

                    if (end && now > end) return "bg-gray-100 text-gray-600";
                    if (start && end && now >= start && now <= end) return "bg-emerald-100 text-emerald-700";
                    if (start && now < start) return "bg-blue-100 text-blue-700";
                    return "bg-gray-100 text-gray-600";
                },

                eventStatusLabel(item) {
                    const now = new Date();
                    const start = item.start_date ? new Date(item.start_date) : null;
                    const end = item.end_date ? new Date(item.end_date) : null;

                    if (end && now > end) return "Selesai";
                    if (start && end && now >= start && now <= end) return "Berlangsung";
                    if (start && now < start) return "Akan Datang";
                    return "—";
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
