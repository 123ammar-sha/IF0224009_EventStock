<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Masuk — EventStock</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        />
        <script
            defer
            src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"
        ></script>
        <script>
            window.APP_CONFIG = {
                apiBaseUrl: '{{ rtrim(config("app.url"), "/") }}/api',
            };
        </script>
        <script src="{{ asset('js/api.js') }}"></script>
    </head>

    <body
        class="bg-gray-50 min-h-screen flex items-center justify-center p-4"
        x-data="loginPage()"
        x-init="init()"
    >
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-8">
                <div
                    class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600
                        rounded-2xl mb-4"
                >
                    <i class="fas fa-boxes-stacked text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">EventStock</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Sistem Manajemen Inventaris
                </p>
            </div>

            <!-- Form card -->
            <div class="bg-white rounded-2xl border border-gray-200 p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">
                    Masuk ke akun Anda
                </h2>

                <div class="space-y-4">
                    <!-- Email -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Email</label
                        >
                        <input
                            type="email"
                            x-model="form.email"
                            @keyup.enter="login()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500
                                  focus:border-transparent"
                            placeholder="admin@eventstock.test"
                        />
                        <p
                            x-show="errors.email"
                            x-text="errors.email"
                            class="text-red-500 text-xs mt-1"
                        ></p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Password</label
                        >
                        <div class="relative">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                x-model="form.password"
                                @keyup.enter="login()"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500
                                      focus:border-transparent pr-10"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400
                                       hover:text-gray-600"
                            >
                                <i
                                    :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                                ></i>
                            </button>
                        </div>
                        <p
                            x-show="errors.password"
                            x-text="errors.password"
                            class="text-red-500 text-xs mt-1"
                        ></p>
                    </div>

                    <!-- Error umum (bukan per-field) -->
                    <div
                        x-show="generalError"
                        class="flex items-center gap-2 p-3 bg-red-50 border border-red-200
                            rounded-lg text-sm text-red-700"
                    >
                        <i class="fas fa-circle-exclamation flex-shrink-0"></i>
                        <span x-text="generalError"></span>
                    </div>

                    <!-- Tombol login -->
                    <button
                        @click="login()"
                        :disabled="loading"
                        class="w-full flex items-center justify-center gap-2 py-2.5 bg-indigo-600
                               text-white rounded-lg font-medium hover:bg-indigo-700
                               disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
                    >
                        <i
                            x-show="loading"
                            class="fas fa-spinner animate-spin"
                        ></i>
                        <span
                            x-text="loading ? 'Memproses...' : 'Masuk'"
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        <script>
            function loginPage() {
                return {
                    form: { email: "", password: "" },
                    errors: {},
                    generalError: "",
                    loading: false,
                    showPassword: false,

                    init() {
                        // Jika sudah login, langsung ke dashboard
                        if (isLoggedIn()) {
                            window.location.href = "/dashboard";
                        }
                    },

                    async login() {
                        this.loading = true;
                        this.errors = {};
                        this.generalError = "";

                        try {
                            const res = await axios.post(
                                window.APP_CONFIG.apiBaseUrl + "/login",
                                this.form,
                                {
                                    headers: {
                                        Accept: "application/json",
                                        "Content-Type": "application/json",
                                    },
                                },
                            );

                            setToken(res.data.access_token);
                            setUser(res.data.user);

                            // Redirect ke dashboard
                            window.location.href = "/dashboard";
                        } catch (e) {
                            if (e.response?.status === 422) {
                                this.errors = parseValidationErrors(e);
                            } else if (e.response?.status === 401) {
                                this.generalError =
                                    e.response.data.message ||
                                    "Email atau password salah.";
                            } else {
                                this.generalError =
                                    "Koneksi ke server gagal. Coba lagi.";
                            }
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }
        </script>
    </body>
</html>
