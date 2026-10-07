<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { usePermission } from '../../Composables/usePermission';
import {
    MapPin,
    Building2,
    Warehouse,
    DoorClosed,
    Map,
    Plus,
    Search,
    RotateCcw,
    Edit3,
    Power,
    Check,
    X,
    AlertTriangle,
    CheckCircle2,
    Layers,
    CornerDownRight,
    Building,
} from 'lucide-vue-next';

const props = defineProps({
    locations: {
        type: Object,
        required: true,
    },
    parentOptions: {
        type: Array,
        default: () => [],
    },
    statistics: {
        type: Object,
        default: () => ({
            total: 0,
            parents: 0,
            children: 0,
            active: 0,
            inactive: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            type: '',
            is_active: '',
            level: '',
            parent_id: '',
        }),
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

const { can, isSuperAdmin } = usePermission();

const canCreate = computed(() => isSuperAdmin.value || can('location.create'));
const canEdit = computed(() => isSuperAdmin.value || can('location.edit'));
const canToggle = computed(() => isSuperAdmin.value || can('location.toggle-status'));

// Filter State
const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const levelFilter = ref(props.filters.level || '');
const statusFilter = ref(props.filters.is_active !== undefined && props.filters.is_active !== null ? String(props.filters.is_active) : '');

const applyFilters = () => {
    router.get(
        '/locations',
        {
            search: search.value || undefined,
            type: typeFilter.value || undefined,
            level: levelFilter.value || undefined,
            is_active: statusFilter.value !== '' ? statusFilter.value : undefined,
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

watch([typeFilter, levelFilter, statusFilter], () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = '';
    typeFilter.value = '';
    levelFilter.value = '';
    statusFilter.value = '';
    router.get('/locations', {}, { preserveState: true, replace: true });
};

// Modal State (Create / Edit)
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentEditingId = ref(null);
const isChildSelection = ref(false);

const locationForm = useForm({
    code: '',
    name: '',
    type: 'room',
    parent_id: null,
    address: '',
    is_active: true,
});

const openCreateModal = (parent = null) => {
    isEditing.value = false;
    currentEditingId.value = null;
    locationForm.reset();
    locationForm.clearErrors();

    if (parent) {
        isChildSelection.value = true;
        locationForm.parent_id = parent.id;
        locationForm.type = 'room';
    } else {
        isChildSelection.value = false;
        locationForm.parent_id = null;
        locationForm.type = 'building';
    }

    locationForm.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (location) => {
    isEditing.value = true;
    currentEditingId.value = location.id;
    locationForm.clearErrors();

    isChildSelection.value = Boolean(location.parent_id);
    locationForm.code = location.code;
    locationForm.name = location.name;
    locationForm.type = location.type;
    locationForm.parent_id = location.parent_id || null;
    locationForm.address = location.address || '';
    locationForm.is_active = Boolean(location.is_active);

    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    locationForm.reset();
    locationForm.clearErrors();
};

const setLevelType = (isChild) => {
    isChildSelection.value = isChild;
    if (!isChild) {
        locationForm.parent_id = null;
        if (locationForm.type === 'room' || locationForm.type === 'area') {
            locationForm.type = 'building';
        }
    } else {
        if (props.parentOptions.length > 0 && !locationForm.parent_id) {
            locationForm.parent_id = props.parentOptions[0].id;
        }
        if (locationForm.type === 'building' || locationForm.type === 'warehouse') {
            locationForm.type = 'room';
        }
    }
};

const submitLocationForm = () => {
    if (isEditing.value) {
        locationForm.put(`/locations/${currentEditingId.value}`, {
            onSuccess: () => {
                closeModal();
            },
        });
    } else {
        locationForm.post('/locations', {
            onSuccess: () => {
                closeModal();
            },
        });
    }
};

// Toggle Status Modal State
const isToggleModalOpen = ref(false);
const selectedLocationForToggle = ref(null);
const toggleLoading = ref(false);

const openToggleModal = (location) => {
    selectedLocationForToggle.value = location;
    isToggleModalOpen.value = true;
};

const closeToggleModal = () => {
    selectedLocationForToggle.value = null;
    isToggleModalOpen.value = false;
};

const confirmToggleStatus = () => {
    if (!selectedLocationForToggle.value) return;

    toggleLoading.value = true;
    router.patch(
        `/locations/${selectedLocationForToggle.value.id}/toggle-status`,
        {},
        {
            preserveState: true,
            onFinish: () => {
                toggleLoading.value = false;
                closeToggleModal();
            },
        }
    );
};

// Icon & Type Helper
const getTypeIcon = (type) => {
    switch (type) {
        case 'building':
            return Building2;
        case 'warehouse':
            return Warehouse;
        case 'room':
            return DoorClosed;
        case 'area':
            return Map;
        default:
            return MapPin;
    }
};

const formatTypeName = (type) => {
    const map = {
        building: 'Gedung',
        warehouse: 'Gudang',
        room: 'Ruangan',
        area: 'Area / Zona',
    };
    return map[type] || type;
};
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { label: 'Master Data' },
            { label: 'Lokasi', href: '/locations' },
        ]"
    >
        <Head title="Manajemen Lokasi" />

        <div class="locations-page-container">
            <!-- Flash Message Alerts -->
            <div v-if="status" class="locations-alert-banner success">
                <div class="alert-banner-left">
                    <CheckCircle2 class="w-5 h-5 flex-shrink-0" />
                    <span>{{ status }}</span>
                </div>
            </div>

            <div v-if="warning" class="locations-alert-banner warning">
                <div class="alert-banner-left">
                    <AlertTriangle class="w-5 h-5 flex-shrink-0" />
                    <span>{{ warning }}</span>
                </div>
            </div>

            <!-- Page Header -->
            <div class="locations-header">
                <div class="locations-title-group">
                    <h1>Manajemen Lokasi</h1>
                    <p>Kelola struktur lokasi fisik, gedung, gudang, dan ruangan untuk penempatan stok & aset.</p>
                </div>

                <div class="header-actions">
                    <button
                        v-if="canCreate"
                        type="button"
                        class="btn-primary-action"
                        @click="openCreateModal()"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Tambah Lokasi</span>
                    </button>
                </div>
            </div>

            <!-- Statistics Overview Cards -->
            <div class="stats-overview-grid">
                <div class="stat-overview-card">
                    <div class="stat-card-icon blue">
                        <MapPin class="w-6 h-6" />
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-val">{{ statistics.total }}</span>
                        <span class="stat-card-lbl">Total Lokasi</span>
                    </div>
                </div>

                <div class="stat-overview-card">
                    <div class="stat-card-icon indigo">
                        <Building class="w-6 h-6" />
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-val">{{ statistics.parents }}</span>
                        <span class="stat-card-lbl">Lokasi Induk (Parent)</span>
                    </div>
                </div>

                <div class="stat-overview-card">
                    <div class="stat-card-icon purple">
                        <Layers class="w-6 h-6" />
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-val">{{ statistics.children }}</span>
                        <span class="stat-card-lbl">Sub-Lokasi (Child)</span>
                    </div>
                </div>

                <div class="stat-overview-card">
                    <div class="stat-card-icon emerald">
                        <CheckCircle2 class="w-6 h-6" />
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-val">{{ statistics.active }}</span>
                        <span class="stat-card-lbl">Lokasi Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="locations-filter-card">
                <div class="filter-grid-row">
                    <!-- Search Input -->
                    <div class="search-input-box">
                        <Search class="w-4 h-4 search-icon-svg" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kode, nama lokasi, atau alamat..."
                            class="locations-input-field"
                        />
                    </div>

                    <!-- Filter Type -->
                    <select v-model="typeFilter" class="locations-select-field">
                        <option value="">Semua Tipe</option>
                        <option value="building">Gedung (Building)</option>
                        <option value="warehouse">Gudang (Warehouse)</option>
                        <option value="room">Ruangan (Room)</option>
                        <option value="area">Area / Zona</option>
                    </select>

                    <!-- Filter Level -->
                    <select v-model="levelFilter" class="locations-select-field">
                        <option value="">Semua Tingkat</option>
                        <option value="parent">Hanya Induk (Parent)</option>
                        <option value="child">Hanya Sub-Lokasi (Child)</option>
                    </select>

                    <!-- Filter Status -->
                    <select v-model="statusFilter" class="locations-select-field">
                        <option value="">Semua Status</option>
                        <option value="true">Aktif</option>
                        <option value="false">Nonaktif</option>
                    </select>

                    <!-- Reset Filter Button -->
                    <button
                        type="button"
                        class="btn-reset-filter"
                        @click="resetFilters"
                        title="Reset Filter"
                    >
                        <RotateCcw class="w-4 h-4" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Main Data Table Container -->
            <div class="locations-table-card">
                <div class="table-responsive-wrapper">
                    <table class="locations-data-table">
                        <thead>
                            <tr>
                                <th>Lokasi / Kode</th>
                                <th>Tingkat</th>
                                <th>Tipe</th>
                                <th>Induk Terkait</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="locations.data && locations.data.length > 0">
                                <tr
                                    v-for="loc in locations.data"
                                    :key="loc.id"
                                    :class="{ 'is-child-row': loc.parent_id }"
                                >
                                    <!-- Name & Code -->
                                    <td>
                                        <div class="loc-identity-cell">
                                            <div
                                                v-if="loc.parent_id"
                                                class="loc-child-indent"
                                                title="Sub-lokasi"
                                            >
                                                <CornerDownRight class="w-4 h-4" />
                                            </div>
                                            <div
                                                class="loc-icon-pill"
                                                :class="loc.parent_id ? 'child' : 'parent'"
                                            >
                                                <component :is="getTypeIcon(loc.type)" class="w-4 h-4" />
                                            </div>
                                            <div class="loc-name-code-wrap">
                                                <span class="loc-item-name">{{ loc.name }}</span>
                                                <span class="loc-item-code">{{ loc.code }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Hierarchy Level -->
                                    <td>
                                        <span
                                            v-if="!loc.parent_id"
                                            class="hierarchy-level-badge parent"
                                        >
                                            Induk (Parent)
                                            <span v-if="loc.children_count > 0" class="text-xs opacity-75">
                                                ({{ loc.children_count }} sub)
                                            </span>
                                        </span>
                                        <span
                                            v-else
                                            class="hierarchy-level-badge child"
                                        >
                                            Sub-Lokasi
                                        </span>
                                    </td>

                                    <!-- Type -->
                                    <td>
                                        <span class="type-badge" :class="loc.type">
                                            <component :is="getTypeIcon(loc.type)" class="w-3 h-3" />
                                            <span>{{ formatTypeName(loc.type) }}</span>
                                        </span>
                                    </td>

                                    <!-- Parent Link -->
                                    <td>
                                        <span v-if="loc.parent" class="text-xs text-muted-foreground font-medium">
                                            {{ loc.parent.name }} ({{ loc.parent.code }})
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground opacity-60">
                                            -
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td>
                                        <span
                                            class="status-badge"
                                            :class="loc.is_active ? 'active' : 'inactive'"
                                        >
                                            <span class="status-dot-indicator"></span>
                                            <span>{{ loc.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td>
                                        <div class="table-actions-group">
                                            <!-- Add child shortcut button -->
                                            <button
                                                v-if="!loc.parent_id && canCreate"
                                                type="button"
                                                class="btn-table-icon-action"
                                                title="Tambah Sub-Lokasi di bawah lokasi ini"
                                                @click="openCreateModal(loc)"
                                            >
                                                <Plus class="w-4 h-4" />
                                            </button>

                                            <!-- Edit button -->
                                            <button
                                                v-if="canEdit"
                                                type="button"
                                                class="btn-table-icon-action edit"
                                                title="Edit Lokasi"
                                                @click="openEditModal(loc)"
                                            >
                                                <Edit3 class="w-4 h-4" />
                                            </button>

                                            <!-- Toggle status button -->
                                            <button
                                                v-if="canToggle"
                                                type="button"
                                                class="btn-table-icon-action"
                                                :class="loc.is_active ? 'toggle-active' : 'toggle-inactive'"
                                                :title="loc.is_active ? 'Nonaktifkan Lokasi' : 'Aktifkan Lokasi'"
                                                @click="openToggleModal(loc)"
                                            >
                                                <Power class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <template v-else>
                                <tr>
                                    <td colspan="6">
                                        <div class="table-empty-state">
                                            <div class="empty-state-icon-box">
                                                <MapPin class="w-8 h-8" />
                                            </div>
                                            <h3>Tidak Ada Data Lokasi</h3>
                                            <p>Belum ada data lokasi yang sesuai dengan filter pencarian Anda.</p>
                                            <button
                                                v-if="canCreate"
                                                type="button"
                                                class="btn-primary-action"
                                                @click="openCreateModal()"
                                            >
                                                <Plus class="w-4 h-4" />
                                                <span>Tambah Lokasi Sekarang</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="locations.links && locations.links.length > 3" class="locations-pagination-container">
                    <div class="pagination-meta-info">
                        Menampilkan <strong>{{ locations.from || 0 }}</strong> - <strong>{{ locations.to || 0 }}</strong> dari <strong>{{ locations.total }}</strong> lokasi
                    </div>

                    <div class="pagination-links-wrap">
                        <template v-for="(link, idx) in locations.links" :key="idx">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="pagination-nav-btn"
                                :class="{ active: link.active }"
                                v-html="link.label"
                                preserve-scroll
                            />
                            <span
                                v-else
                                class="pagination-nav-btn disabled"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- CREATE / EDIT MODAL -->
        <div v-if="isModalOpen" class="modal-backdrop-overlay" @click.self="closeModal">
            <div class="location-modal-dialog">
                <div class="modal-header-bar">
                    <h2>{{ isEditing ? 'Edit Data Lokasi' : 'Tambah Lokasi Baru' }}</h2>
                    <button type="button" class="btn-modal-close" @click="closeModal">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitLocationForm">
                    <div class="modal-body-content modal-form-stack">
                        <!-- Level Type Toggle -->
                        <div class="form-field-group">
                            <label>Tingkat Lokasi</label>
                            <div class="level-selector-row">
                                <button
                                    type="button"
                                    class="level-option-btn"
                                    :class="{ active: !isChildSelection }"
                                    @click="setLevelType(false)"
                                >
                                    <Building2 class="w-4 h-4" />
                                    <span>Lokasi Induk (Parent)</span>
                                </button>
                                <button
                                    type="button"
                                    class="level-option-btn"
                                    :class="{ active: isChildSelection }"
                                    @click="setLevelType(true)"
                                >
                                    <Layers class="w-4 h-4" />
                                    <span>Sub-Lokasi (Child)</span>
                                </button>
                            </div>
                        </div>

                        <!-- Parent Selector (If Child) -->
                        <div v-if="isChildSelection" class="form-field-group">
                            <label>Pilih Lokasi Induk (Parent) <span class="required-mark">*</span></label>
                            <select
                                v-model="locationForm.parent_id"
                                class="locations-select-field"
                                style="width: 100%;"
                            >
                                <option :value="null" disabled>-- Pilih Lokasi Induk --</option>
                                <option
                                    v-for="parent in parentOptions"
                                    :key="parent.id"
                                    :value="parent.id"
                                    :disabled="isEditing && parent.id === currentEditingId"
                                >
                                    {{ parent.name }} ({{ parent.code }}) — {{ formatTypeName(parent.type) }}
                                </option>
                            </select>
                            <span v-if="locationForm.errors.parent_id" class="form-field-error">
                                {{ locationForm.errors.parent_id }}
                            </span>
                        </div>

                        <!-- Code & Name Row -->
                        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 12px;">
                            <div class="form-field-group">
                                <label>Kode Lokasi <span class="required-mark">*</span></label>
                                <input
                                    v-model="locationForm.code"
                                    type="text"
                                    placeholder="LOC-001"
                                    maxlength="20"
                                    style="text-transform: uppercase;"
                                    required
                                />
                                <span v-if="locationForm.errors.code" class="form-field-error">
                                    {{ locationForm.errors.code }}
                                </span>
                            </div>

                            <div class="form-field-group">
                                <label>Nama Lokasi <span class="required-mark">*</span></label>
                                <input
                                    v-model="locationForm.name"
                                    type="text"
                                    placeholder="Contoh: Gedung Utama / Ruang Server"
                                    maxlength="100"
                                    required
                                />
                                <span v-if="locationForm.errors.name" class="form-field-error">
                                    {{ locationForm.errors.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Type Selection -->
                        <div class="form-field-group">
                            <label>Tipe Lokasi <span class="required-mark">*</span></label>
                            <select v-model="locationForm.type" class="locations-select-field" style="width: 100%;">
                                <option value="building">Gedung (Building)</option>
                                <option value="warehouse">Gudang (Warehouse)</option>
                                <option value="room">Ruangan (Room)</option>
                                <option value="area">Area / Zona</option>
                            </select>
                            <span v-if="locationForm.errors.type" class="form-field-error">
                                {{ locationForm.errors.type }}
                            </span>
                        </div>

                        <!-- Address / Description -->
                        <div class="form-field-group">
                            <label>Alamat / Keterangan Lokasi</label>
                            <textarea
                                v-model="locationForm.address"
                                placeholder="Detail alamat atau posisi lokasi..."
                                maxlength="500"
                            ></textarea>
                            <span v-if="locationForm.errors.address" class="form-field-error">
                                {{ locationForm.errors.address }}
                            </span>
                        </div>

                        <!-- Active Checkbox -->
                        <div class="form-field-group">
                            <label class="form-checkbox-label">
                                <input
                                    v-model="locationForm.is_active"
                                    type="checkbox"
                                />
                                <span>Status Lokasi Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer-bar">
                        <button type="button" class="btn-modal-cancel" @click="closeModal">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="btn-modal-submit"
                            :disabled="locationForm.processing"
                        >
                            <Check class="w-4 h-4" />
                            <span>{{ isEditing ? 'Simpan Perubahan' : 'Tambah Lokasi' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TOGGLE STATUS CONFIRMATION MODAL -->
        <div v-if="isToggleModalOpen" class="modal-backdrop-overlay" @click.self="closeToggleModal">
            <div class="location-modal-dialog" style="max-width: 440px;">
                <div class="modal-header-bar">
                    <h2>Konfirmasi Ubah Status</h2>
                    <button type="button" class="btn-modal-close" @click="closeToggleModal">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="modal-body-content" style="text-align: center; padding: 24px 20px;">
                    <div
                        class="empty-state-icon-box"
                        style="margin: 0 auto 16px auto;"
                        :style="{
                            background: selectedLocationForToggle?.is_active ? 'rgba(239, 68, 68, 0.12)' : 'rgba(16, 185, 129, 0.12)',
                            color: selectedLocationForToggle?.is_active ? '#ef4444' : '#10b981'
                        }"
                    >
                        <Power class="w-7 h-7" />
                    </div>

                    <h3 style="font-size: 1.125rem; font-weight: 700; margin-bottom: 8px;">
                        {{ selectedLocationForToggle?.is_active ? 'Nonaktifkan Lokasi?' : 'Aktifkan Lokasi?' }}
                    </h3>

                    <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5;">
                        Apakah Anda yakin ingin mengubah status lokasi
                        <strong>{{ selectedLocationForToggle?.name }}</strong> ({{ selectedLocationForToggle?.code }})
                        menjadi
                        <strong>{{ selectedLocationForToggle?.is_active ? 'Nonaktif' : 'Aktif' }}</strong>?
                    </p>
                </div>

                <div class="modal-footer-bar">
                    <button type="button" class="btn-modal-cancel" @click="closeToggleModal">
                        Batal
                    </button>
                    <button
                        type="button"
                        class="btn-modal-submit"
                        :style="{
                            background: selectedLocationForToggle?.is_active ? '#ef4444' : '#10b981'
                        }"
                        :disabled="toggleLoading"
                        @click="confirmToggleStatus"
                    >
                        <span>{{ selectedLocationForToggle?.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style src="@/../css/pages/locations.css" scoped></style>
