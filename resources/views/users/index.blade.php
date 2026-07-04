@extends('layouts.app')
@section('title', 'Pengguna')
@section('page-title', 'Manajemen Pengguna')
@section('content')
<div x-data="usersPage()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
        <div class="relative flex-1 max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input
                type="text"
                x-model="search"
                @input="onSearch()"
                placeholder="Cari pengguna..."
                class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>
        <button
            @click="openCreateModal()"
            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white
                   rounded-lg text-sm hover:bg-indigo-700 whitespace-nowrap ml-3"
        >
            <i class="fas fa-plus text-xs"></i>
            Tambah Pengguna
        </button>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-20 text-gray-400">
            <i class="fas fa-spinner animate-spin text-xl mr-2"></i> Memuat...
        </div>

        <div x-show="!loading && items.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400">
            <i class="fas fa-users text-4xl mb-3 opacity-30"></i>
            <p class="text-sm">Belum ada pengguna</p>
            <button @click="openCreateModal()" class="mt-3 text-sm text-indigo-600 hover:underline">
                Tambah yang pertama
            </button>
        </div>

        <div x-show="!loading && items.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Bergabung</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="item in items" :key="item.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-gray-900" x-text="item.name"></td>
                            <td class="px-5 py-3.5 text-gray-600" x-text="item.email"></td>
                            <td class="px-5 py-3.5">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="roleClass(item.role)"
                                    x-text="roleLabel(item.role)"
                                ></span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs" x-text="formatDate(item.created_at)"></td>
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
    <div
        x-show="showModal"
        x-transition:enter="transition duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    >
        <div @click.stop class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900"
                    x-text="editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru'"></h3>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4">
                <!-- Nama -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" x-model="formData.name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Contoh: Ahmad Setiawan" />
                    <p x-show="formErrors.name" x-text="formErrors.name" class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" x-model="formData.email"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="contoh@eventstock.test" />
                    <p x-show="formErrors.email" x-text="formErrors.email" class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                        <span x-show="editMode" class="text-gray-400 font-normal">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" x-model="formData.password"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••" />
                    <p x-show="formErrors.password" x-text="formErrors.password" class="text-red-500 text-xs mt-1"></p>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select x-model="formData.role"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih role...</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="warehouse_manager">Manajer Gudang</option>
                        <option value="field_crew">Tim Lapangan</option>
                    </select>
                    <p x-show="formErrors.role" x-text="formErrors.role" class="text-red-500 text-xs mt-1"></p>
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
    function usersPage() {
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
                requireSuperAdmin(); // Hanya super_admin yang boleh akses
                await this.fetchData();
            },

            async fetchData() {
                this.loading = true;
                try {
                    const params = { per_page: 10, page: this.currentPage };
                    if (this.search) params.search = this.search;

                    const res = await apiRequest("GET", "/users", null, params);
                    this.items = res.data.data || res.data;
                    this.totalPages = res.data.last_page || 1;
                } catch (e) {
                    toastError("Gagal memuat data pengguna");
                } finally {
                    this.loading = false;
                }
            },

            openCreateModal() {
                this.editMode = false;
                this.formData = { role: 'field_crew' };
                this.formErrors = {};
                this.showModal = true;
            },

            edit(item) {
                this.editMode = true;
                this.formData = {
                    id: item.id,
                    name: item.name,
                    email: item.email,
                    role: item.role,
                    password: "",
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
                    const payload = { ...this.formData };
                    // Hapus password kosong saat edit
                    if (this.editMode && !payload.password) {
                        delete payload.password;
                    }

                    if (this.editMode) {
                        await apiRequest("PUT", "/users/" + payload.id, payload);
                        toastSuccess("Pengguna berhasil diupdate");
                    } else {
                        await apiRequest("POST", "/users", payload);
                        toastSuccess("Pengguna berhasil ditambahkan");
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
                        await apiRequest("DELETE", "/users/" + id);
                        toastSuccess("Pengguna berhasil dihapus");
                        await this.fetchData();
                    } catch (e) {
                        toastError(parseError(e));
                    }
                }, "Hapus pengguna ini?", "Akun pengguna akan dihapus permanen.");
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

            roleClass(role) {
                return ({
                    super_admin: "bg-indigo-100 text-indigo-700",
                    warehouse_manager: "bg-emerald-100 text-emerald-700",
                    field_crew: "bg-sky-100 text-sky-700",
                }[role] || "bg-gray-100 text-gray-600");
            },

            roleLabel(role) {
                return ({
                    super_admin: "Super Admin",
                    warehouse_manager: "Manajer Gudang",
                    field_crew: "Tim Lapangan",
                }[role] || role);
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
