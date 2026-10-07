<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
import {
    Search,
    Filter,
    UserCheck,
    Check,
    X,
    Clock,
    AlertCircle,
    Building2,
    Shield,
    RotateCcw,
    Sparkles,
} from 'lucide-vue-next';

const props = defineProps({
    pendingUsers: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    availableRoles: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '', department_id: '' }),
    },
    status: {
        type: String,
        default: null,
    },
    warning: {
        type: String,
        default: null,
    },
});

// Filters
const search = ref(props.filters.search || '');
const departmentId = ref(props.filters.department_id || '');

const applyFilters = () => {
    router.get(
        '/settings/user-registrations',
        {
            search: search.value || undefined,
            department_id: departmentId.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

let searchDebounceTimeout = null;
watch(search, () => {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch(departmentId, () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    departmentId.value = '';
    router.get('/settings/user-registrations', {}, { preserveState: true, replace: true });
};

// Modal: Approve
const selectedUserForApprove = ref(null);
const approveForm = useForm({
    roles: ['Requester'],
});

const openApproveModal = (user) => {
    selectedUserForApprove.value = user;
    approveForm.roles = ['Requester'];
    approveForm.clearErrors();
};

const closeApproveModal = () => {
    selectedUserForApprove.value = null;
    approveForm.reset();
};

const toggleRole = (roleName) => {
    const idx = approveForm.roles.indexOf(roleName);
    if (idx > -1) {
        if (approveForm.roles.length > 1) {
            approveForm.roles.splice(idx, 1);
        }
    } else {
        approveForm.roles.push(roleName);
    }
};

const submitApprove = () => {
    if (!selectedUserForApprove.value) return;

    approveForm.post(`/settings/user-registrations/${selectedUserForApprove.value.id}/approve`, {
        onSuccess: () => {
            closeApproveModal();
        },
    });
};

// Modal: Reject
const selectedUserForReject = ref(null);
const rejectForm = useForm({});

const openRejectModal = (user) => {
    selectedUserForReject.value = user;
};

const closeRejectModal = () => {
    selectedUserForReject.value = null;
};

const submitReject = () => {
    if (!selectedUserForReject.value) return;

    rejectForm.post(`/settings/user-registrations/${selectedUserForReject.value.id}/reject`, {
        onSuccess: () => {
            closeRejectModal();
        },
    });
};

// Helper for Initials
const getInitials = (name) => {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

// Helper for Date Format
const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(dateStr));
};

// Helper descriptions for roles
const getRoleDescription = (roleName) => {
    switch (roleName) {
        case 'Super Admin':
            return 'Akses penuh seluruh sistem & konfigurasi approval.';
        case 'Admin':
            return 'Pengelolaan data master, user, dan review transaksi.';
        case 'Inventory Staff':
            return 'Pencatatan mutasi stok, barang masuk/keluar, & opname.';
        case 'Asset Staff':
            return 'Registrasi aset tetap, mutasi, pemeliharaan, & disposal.';
        case 'Requester':
            return 'Mengajukan permintaan barang dari inventaris.';
        case 'Manager':
            return 'Penyetuju (approver) transaksi operasional tingkat akhir.';
        default:
            return 'Hak akses operasional sistem.';
    }
};
</script>

<template>
    <Head title="Persetujuan Registrasi — AssetFlow" />

    <AppLayout>
        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="page-title-group">
                    <h1>Persetujuan Registrasi Pengguna</h1>
                    <p>Tinjau dan proses pendaftaran mandiri dari calon pengguna baru</p>
                </div>
                <div class="stat-badge">
                    <Clock class="w-4 h-4" />
                    <span>{{ pendingUsers.total }} Menunggu Persetujuan</span>
                </div>
            </div>

            <!-- Flash Alert Banner -->
            <div v-if="warning" class="alert-warning-banner" style="margin-bottom: 20px;">
                <AlertCircle class="w-5 h-5 flex-shrink-0" />
                <span>{{ warning }}</span>
            </div>
            <div v-else-if="status" class="alert-success-banner" style="margin-bottom: 20px;">
                <UserCheck class="w-5 h-5 flex-shrink-0" />
                <span>{{ status }}</span>
            </div>

            <!-- Filter & Search Card -->
            <div class="filter-card">
                <div class="filter-grid">
                    <div class="filter-search-wrapper">
                        <Search class="filter-search-icon w-4 h-4" />
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Cari nama, email, atau NIP..."
                            class="filter-input"
                        />
                    </div>

                    <select v-model="departmentId" class="filter-select">
                        <option value="">Semua Departemen</option>
                        <option 
                            v-for="dept in departments" 
                            :key="dept.id" 
                            :value="dept.id"
                        >
                            {{ dept.name }} ({{ dept.code }})
                        </option>
                    </select>

                    <button 
                        v-if="search || departmentId" 
                        type="button" 
                        class="btn-filter-reset" 
                        @click="resetFilters"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Calon Pengguna</th>
                                <th>NIP</th>
                                <th>Departemen</th>
                                <th>Jabatan & Kontak</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in pendingUsers.data" :key="u.id">
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar-circle">
                                            {{ getInitials(u.name) }}
                                        </div>
                                        <div>
                                            <div class="user-name-title">{{ u.name }}</div>
                                            <div class="user-email-sub">{{ u.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span v-if="u.employee_id" class="font-mono text-sm">{{ u.employee_id }}</span>
                                    <span v-else class="text-tertiary">-</span>
                                </td>
                                <td>
                                    <div v-if="u.department" class="flex items-center gap-1.5 font-medium">
                                        <Building2 class="w-3.5 h-3.5 text-secondary" />
                                        <span>{{ u.department.name }}</span>
                                    </div>
                                    <span v-else class="text-tertiary">-</span>
                                </td>
                                <td>
                                    <div class="font-medium">{{ u.position || '-' }}</div>
                                    <div v-if="u.phone_number" class="text-xs text-tertiary">{{ u.phone_number }}</div>
                                </td>
                                <td>
                                    <span class="text-xs text-secondary">{{ formatDate(u.created_at) }}</span>
                                </td>
                                <td>
                                    <span class="badge-pending">
                                        <Clock class="w-3 h-3" />
                                        <span>Pending</span>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <button 
                                            type="button" 
                                            class="btn-action-approve"
                                            @click="openApproveModal(u)"
                                            title="Setujui pendaftaran"
                                        >
                                            <Check class="w-3.5 h-3.5" />
                                            <span>Setujui</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="btn-action-reject"
                                            @click="openRejectModal(u)"
                                            title="Tolak pendaftaran"
                                        >
                                            <X class="w-3.5 h-3.5" />
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="pendingUsers.data.length === 0">
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <UserCheck class="w-8 h-8" />
                                        </div>
                                        <h3>Tidak Ada Pendaftaran Menunggu</h3>
                                        <p>Semua pendaftaran mandiri dari calon pengguna telah ditinjau atau belum ada pendaftaran baru.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pendingUsers.links && pendingUsers.links.length > 3" class="pagination-wrapper">
                    <div class="text-xs text-secondary">
                        Menampilkan {{ pendingUsers.from || 0 }} sampai {{ pendingUsers.to || 0 }} dari {{ pendingUsers.total }} data
                    </div>
                    <div class="pagination-links">
                        <Component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, idx) in pendingUsers.links"
                            :key="idx"
                            :href="link.url || undefined"
                            class="pagination-btn"
                            :class="{
                                active: link.active,
                                disabled: !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL APPROVE -->
        <div v-if="selectedUserForApprove" class="modal-backdrop" @click.self="closeApproveModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <Check class="w-4 h-4" />
                        </div>
                        <h3 class="modal-title">Setujui Pendaftaran Pengguna</h3>
                    </div>
                    <button type="button" class="modal-close-btn" @click="closeApproveModal">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitApprove">
                    <div class="modal-body">
                        <!-- User Summary Card -->
                        <div class="user-summary-card">
                            <div class="user-summary-grid">
                                <div>
                                    <div class="summary-item-label">Nama Lengkap</div>
                                    <div class="summary-item-val">{{ selectedUserForApprove.name }}</div>
                                </div>
                                <div>
                                    <div class="summary-item-label">Email Korporat</div>
                                    <div class="summary-item-val">{{ selectedUserForApprove.email }}</div>
                                </div>
                                <div>
                                    <div class="summary-item-label">Departemen</div>
                                    <div class="summary-item-val">{{ selectedUserForApprove.department?.name || '-' }}</div>
                                </div>
                                <div>
                                    <div class="summary-item-label">NIP / Jabatan</div>
                                    <div class="summary-item-val">{{ selectedUserForApprove.employee_id || '-' }} / {{ selectedUserForApprove.position || '-' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label class="form-label font-semibold text-primary block mb-1">
                                Pilih Role Pengguna <span class="text-required">*</span>
                            </label>
                            <p class="text-xs text-secondary mb-3">
                                Pilih minimal 1 peran akses yang akan diberikan kepada pengguna ini:
                            </p>

                            <div class="role-selection-group">
                                <div 
                                    v-for="role in availableRoles" 
                                    :key="role.id" 
                                    class="role-checkbox-item"
                                    :class="{ 'is-selected': approveForm.roles.includes(role.name) }"
                                    @click="toggleRole(role.name)"
                                >
                                    <input 
                                        type="checkbox" 
                                        :value="role.name" 
                                        :checked="approveForm.roles.includes(role.name)"
                                        class="checkbox-custom mt-0.5"
                                        @click.stop="toggleRole(role.name)"
                                    />
                                    <div class="role-info">
                                        <h4>{{ role.name }}</h4>
                                        <p>{{ getRoleDescription(role.name) }}</p>
                                    </div>
                                </div>
                            </div>

                            <p v-if="approveForm.errors.roles" class="input-error-msg">
                                {{ approveForm.errors.roles }}
                            </p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" @click="closeApproveModal" :disabled="approveForm.processing">
                            Batal
                        </button>
                        <button type="submit" class="btn-modal-confirm-approve" :disabled="approveForm.processing || approveForm.roles.length === 0">
                            <Check class="w-4 h-4" />
                            <span>{{ approveForm.processing ? 'Memproses...' : 'Setujui & Aktifkan Akun' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL REJECT -->
        <div v-if="selectedUserForReject" class="modal-backdrop" @click.self="closeRejectModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center">
                            <X class="w-4 h-4" />
                        </div>
                        <h3 class="modal-title">Konfirmasi Penolakan</h3>
                    </div>
                    <button type="button" class="modal-close-btn" @click="closeRejectModal">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitReject">
                    <div class="modal-body">
                        <div class="user-summary-card mb-4">
                            <div class="text-sm font-semibold text-primary mb-1">{{ selectedUserForReject.name }}</div>
                            <div class="text-xs text-secondary">{{ selectedUserForReject.email }} • {{ selectedUserForReject.department?.name || 'Tanpa Departemen' }}</div>
                        </div>

                        <p class="text-sm text-secondary leading-relaxed">
                            Apakah Anda yakin ingin menolak pengajuan pendaftaran mandiri untuk akun ini? Status pendaftaran akan diubah menjadi <strong class="text-red-500">Rejected</strong> dan pengguna tidak dapat melakukan login ke sistem.
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" @click="closeRejectModal" :disabled="rejectForm.processing">
                            Batal
                        </button>
                        <button type="submit" class="btn-modal-confirm-reject" :disabled="rejectForm.processing">
                            <X class="w-4 h-4" />
                            <span>{{ rejectForm.processing ? 'Memproses...' : 'Konfirmasi Tolak' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style src="@/../css/pages/user-registrations.css" scoped></style>
<style src="@/../css/pages/login.css" scoped></style>
