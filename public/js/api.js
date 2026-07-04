const API_BASE = window.APP_CONFIG?.apiBaseUrl || '/api';

function getToken() {
    return localStorage.getItem("eventstock_token");
}

function setToken(token) {
    localStorage.setItem("eventstock_token", token);
}

function clearToken() {
    localStorage.removeItem("eventstock_token");
    localStorage.removeItem("eventstock_user");
}

function setUser(user) {
    localStorage.setItem("eventstock_user", JSON.stringify(user));
}

function getUser() {
    const raw = localStorage.getItem("eventstock_user");
    return raw ? JSON.parse(raw) : null;
}

function isLoggedIn() {
    return !!getToken();
}

function getUserRole() {
    const user = getUser();
    return user?.role || null;
}

async function apiRequest(method, endpoint, data = null, params = {}) {
    const url = `${API_BASE}${endpoint}`;

    const config = {
        method: method.toUpperCase(),
        url: url,  // ← Gunakan url, bukan API_BASE + endpoint
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
        },
        params: params,
    };

    const token = getToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    if (data !== null) {
        config.data = data;
    }

    try {
        const response = await axios(config);
        return response.data;
    } catch (error) {
        console.error('API Error:', error.response?.status, error.response?.data);

        if (error.response?.status === 401) {
            clearToken();
            if (!window.location.pathname.includes('/login')) {
                window.location.href = "/login";
            }
            return;
        }

        if (error.response?.status === 403) {
            if (!window.location.pathname.includes('/403')) {
                window.location.href = "/403";
            }
            return;
        }
        throw error;
    }
}

function toastSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

function toastError(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "error",
        title: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
    });
}

function toastWarning(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "warning",
        title: message,
        showConfirmButton: false,
        timer: 4000,
    });
}

function confirmAction(
    callback,
    title = "Yakin?",
    text = "Tindakan ini tidak bisa dibatalkan.",
) {
    Swal.fire({
        title: title,
        text: text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc2626",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Ya, lanjutkan",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) callback();
    });
}

function parseError(error) {
    if (!error.response) return "Koneksi gagal. Periksa jaringan Anda.";

    const { status, data } = error.response;

    if (status === 422) {
        // Validasi error — ambil pesan pertama dari field manapun
        if (data.errors) {
            const firstKey = Object.keys(data.errors)[0];
            return data.errors[firstKey][0];
        }
        return data.message || "Data tidak valid.";
    }

    if (status === 404) return data.message || "Data tidak ditemukan.";
    if (status === 403) return "Anda tidak punya akses untuk tindakan ini.";
    if (status === 500)
        return "Terjadi kesalahan server. Hubungi administrator.";

    return data.message || "Terjadi kesalahan.";
}

function parseValidationErrors(error) {
    if (error.response?.status === 422 && error.response?.data?.errors) {
        const result = {};
        Object.keys(error.response.data.errors).forEach((key) => {
            result[key] = error.response.data.errors[key][0];
        });
        return result;
    }
    return {};
}

function showLoading(message = "Memproses...") {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading(),
    });
}

function hideLoading() {
    Swal.close();
}

function requireAuth() {
    if (!isLoggedIn()) {
        window.location.href = "/login";
    }
}

function requireSuperAdmin() {
    requireAuth();
    if (getUserRole() !== "super_admin") {
        window.location.href = "/403";
    }
}

/**
 * Periksa apakah user yang login adalah field_crew
 */
function isCrew() {
    return getUserRole() === "field_crew";
}

/**
 * Redirect ke /403 jika user adalah field_crew
 */
function requireNotCrew() {
    requireAuth();
    if (getUserRole() === "field_crew") {
        window.location.href = "/403";
    }
}

window.apiRequest = apiRequest;
window.getToken = getToken;
window.setToken = setToken;
window.clearToken = clearToken;
window.setUser = setUser;
window.getUser = getUser;
window.isLoggedIn = isLoggedIn;
window.getUserRole = getUserRole;
window.toastSuccess = toastSuccess;
window.toastError = toastError;
window.toastWarning = toastWarning;
window.confirmAction = confirmAction;
window.parseError = parseError;
window.parseValidationErrors = parseValidationErrors;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.requireAuth = requireAuth;
window.requireSuperAdmin = requireSuperAdmin;
window.isCrew = isCrew;
window.requireNotCrew = requireNotCrew;
