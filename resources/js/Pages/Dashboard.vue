<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import {
    Package,
    Boxes,
    Wrench,
    CircleDollarSign,
    Calendar,
    ArrowUpRight,
    Activity,
    AlertTriangle,
    CheckCircle2,
    Clock,
    Sparkles,
    Building2,
    ShieldCheck,
    ChevronDown,
    Filter,
} from 'lucide-vue-next';

const props = defineProps({
    user: {
        type: Object,
        default: () => ({
            name: 'Pengguna',
            department: 'Semua Departemen',
            roles: [],
        }),
    },
});

const todayFormatted = computed(() => {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date());
});

const primaryRole = computed(() => {
    if (props.user?.roles && props.user.roles.length > 0) {
        return props.user.roles[0].replace(/[-_]/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    }
    return 'Staff Operasional';
});

// Period & Date Range Filter (Static / Frontend Interactive State)
const isFilterOpen = ref(false);
const filterRef = ref(null);
const selectedPreset = ref('this_month');
const customStartDate = ref('2026-10-01');
const customEndDate = ref('2026-10-31');
const appliedFilterLabel = ref('Bulan Ini (Okt 2026)');

const presets = [
    { key: 'today', label: 'Hari Ini' },
    { key: '7_days', label: '7 Hari Terakhir' },
    { key: 'this_month', label: 'Bulan Ini' },
    { key: 'this_year', label: 'Tahun 2026' },
];

const toggleFilter = (e) => {
    e.stopPropagation();
    isFilterOpen.value = !isFilterOpen.value;
};

const selectPreset = (presetKey, label) => {
    selectedPreset.value = presetKey;
    if (presetKey === 'today') {
        appliedFilterLabel.value = 'Hari Ini (02 Okt)';
    } else if (presetKey === '7_days') {
        appliedFilterLabel.value = '7 Hari Terakhir';
    } else if (presetKey === 'this_month') {
        appliedFilterLabel.value = 'Bulan Ini (Okt 2026)';
    } else if (presetKey === 'this_year') {
        appliedFilterLabel.value = 'Tahun 2026';
    }
    isFilterOpen.value = false;
};

const applyCustomRange = () => {
    selectedPreset.value = 'custom';
    if (customStartDate.value && customEndDate.value) {
        appliedFilterLabel.value = `${customStartDate.value} s/d ${customEndDate.value}`;
    }
    isFilterOpen.value = false;
};

const resetFilter = () => {
    selectedPreset.value = 'this_month';
    customStartDate.value = '2026-10-01';
    customEndDate.value = '2026-10-31';
    appliedFilterLabel.value = 'Bulan Ini (Okt 2026)';
    isFilterOpen.value = false;
};

const handleClickOutside = (e) => {
    if (filterRef.value && !filterRef.value.contains(e.target)) {
        isFilterOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const stats = [
    {
        title: 'Total Aset Terdata',
        value: '2.847',
        badge: '+12% bln ini',
        badgeType: 'positive',
        subtitle: 'Aset bernilai aktif di seluruh cabang',
        icon: Package,
        iconVariant: 'dash-stat-icon-primary',
    },
    {
        title: 'Item Stok Tersedia',
        value: '2.180',
        badge: '76% optimal',
        badgeType: 'positive',
        subtitle: 'Siap dialokasikan untuk operasional',
        icon: Boxes,
        iconVariant: 'dash-stat-icon-success',
    },
    {
        title: 'Dalam Pemeliharaan',
        value: '42',
        badge: '3 jatuh tempo',
        badgeType: 'warning',
        subtitle: 'Perbaikan teknis & inspeksi berkala',
        icon: Wrench,
        iconVariant: 'dash-stat-icon-warning',
    },
    {
        title: 'Total Valuasi Aset',
        value: 'Rp 4,2 M',
        badge: '+8% YoY',
        badgeType: 'positive',
        subtitle: 'Akumulasi nilai buku inventaris aktif',
        icon: CircleDollarSign,
        iconVariant: 'dash-stat-icon-info',
    },
];

const categories = [
    { name: 'Peralatan & Hardware TI', value: 842, percent: 100, barClass: 'bar-blue' },
    { name: 'Perabot & Furnitur Kantor', value: 456, percent: 54, barClass: 'bar-teal' },
    { name: 'Peralatan Elektronik & Listrik', value: 398, percent: 47, barClass: 'bar-amber' },
    { name: 'Perlengkapan & ATK Konsumabel', value: 350, percent: 41, barClass: 'bar-purple' },
    { name: 'Kendaraan & Transportasi', value: 234, percent: 27, barClass: 'bar-rose' },
];

const activities = [
    {
        title: 'Laptop MacBook Pro #AST-2026-084 diserahkan ke Divisi Rekayasa',
        time: '2 jam yang lalu',
        icon: CheckCircle2,
    },
    {
        title: 'Server Rack #SRV-102 dijadwalkan masuk pemeliharaan rutin',
        time: '5 jam yang lalu',
        icon: Clock,
    },
    {
        title: 'Penerimaan stok barang ATK baru (24 item logistik masuk)',
        time: 'Kemarin, 16:40',
        icon: Package,
    },
    {
        title: 'Kendaraan Operasional #VAN-09 selesai masa sewa dan dikembalikan',
        time: 'Kemarin, 11:15',
        icon: CheckCircle2,
    },
    {
        title: '15 Unit Monitor Dell 27-inch berhasil ditambahkan ke inventaris',
        time: '3 hari yang lalu',
        icon: Boxes,
    },
];

const attentionItems = [
    {
        title: 'Forklift Gudang Barat #FL-042',
        desc: 'Jadwal servis berkala tahunan melewati batas waktu 3 hari',
        priority: 'Prioritas Tinggi',
        badgeClass: 'dash-badge-danger',
    },
    {
        title: 'Baterai UPS Ruang Server',
        desc: 'Kapasitas daya menurun di bawah 20%, butuh penggantian unit',
        priority: 'Prioritas Tinggi',
        badgeClass: 'dash-badge-danger',
    },
    {
        title: 'Tinta Printer Epson L3110',
        desc: 'Sisa kuantitas di bawah ambang batas minimum (3 botol tersisa)',
        priority: 'Perlu Reorder',
        badgeClass: 'dash-badge-warning',
    },
    {
        title: 'Mobil Operasional Avanza #B-1290-XYZ',
        desc: 'Perpanjangan uji berkala & STNK jatuh tempo 7 hari ke depan',
        priority: 'Tinjauan Segera',
        badgeClass: 'dash-badge-warning',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="[{ label: 'Dashboard' }]">
        <Head title="Dashboard — AssetFlow Inventory" />

        <div class="dash-container">
            <!-- Header Salam & Filter Periode -->
            <div class="dash-header">
                <div class="dash-header-left">
                    <div class="dash-title-row">
                        <h1 class="dash-title">Selamat Datang, {{ user.name }}!</h1>
                        <span class="dash-user-badge">
                            <ShieldCheck class="w-3.5 h-3.5" />
                            {{ primaryRole }}
                        </span>
                        <span v-if="user.department" class="dash-user-badge">
                            <Building2 class="w-3.5 h-3.5" />
                            {{ user.department }}
                        </span>
                    </div>
                    <p class="dash-subtitle">
                        Ringkasan metrik operasional sistem manajemen aset dan logistik inventaris
                    </p>
                </div>

                <div class="dash-header-right">
                    <!-- Date & Period Filter Trigger & Popover -->
                    <div ref="filterRef" class="dash-filter-wrapper">
                        <button
                            type="button"
                            class="dash-filter-trigger"
                            @click="toggleFilter"
                            aria-label="Filter Periode"
                        >
                            <Calendar class="w-4 h-4 text-primary" />
                            <span>{{ appliedFilterLabel }}</span>
                            <ChevronDown
                                class="dash-filter-chevron"
                                :class="{ rotated: isFilterOpen }"
                            />
                        </button>

                        <!-- Popover Pilihan Rentang Tanggal -->
                        <div v-if="isFilterOpen" class="dash-filter-popover">
                            <!-- Preset Cepat -->
                            <div>
                                <div class="dash-filter-section-title">Pilihan Periode Cepat</div>
                                <div class="dash-presets-grid">
                                    <button
                                        v-for="preset in presets"
                                        :key="preset.key"
                                        type="button"
                                        class="dash-preset-btn"
                                        :class="{ active: selectedPreset === preset.key }"
                                        @click="selectPreset(preset.key, preset.label)"
                                    >
                                        {{ preset.label }}
                                    </button>
                                </div>
                            </div>

                            <div class="dash-filter-divider"></div>

                            <!-- Rentang Kustom (Dari Tanggal s/d Sampai Tanggal) -->
                            <div class="dash-custom-range">
                                <div class="dash-filter-section-title">Rentang Tanggal Kustom</div>
                                <div class="dash-date-inputs">
                                    <div class="dash-date-field">
                                        <label class="dash-date-label">Dari Tanggal:</label>
                                        <input
                                            type="date"
                                            v-model="customStartDate"
                                            class="dash-input-date"
                                        />
                                    </div>
                                    <div class="dash-date-field">
                                        <label class="dash-date-label">Sampai Tanggal:</label>
                                        <input
                                            type="date"
                                            v-model="customEndDate"
                                            class="dash-input-date"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Aksi Tombol -->
                            <div class="dash-filter-actions">
                                <button
                                    type="button"
                                    class="dash-btn-reset"
                                    @click="resetFilter"
                                >
                                    Reset
                                </button>
                                <button
                                    type="button"
                                    class="dash-btn-apply"
                                    @click="applyCustomRange"
                                >
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="dash-date-pill">
                        <Clock class="w-4 h-4 text-primary" />
                        <span>{{ todayFormatted }}</span>
                    </div>
                </div>
            </div>

            <!-- Grid 4 Kartu KPI Ringkasan -->
            <div class="dash-grid dash-stats-row">
                <div v-for="stat in stats" :key="stat.title" class="dash-card dash-stat-card">
                    <div class="dash-stat-top">
                        <div class="dash-stat-label-wrap">
                            <div class="dash-stat-icon-wrapper" :class="stat.iconVariant">
                                <component :is="stat.icon" class="w-4 h-4" />
                            </div>
                            <span class="dash-stat-label">{{ stat.title }}</span>
                        </div>
                        <span
                            class="dash-badge"
                            :class="{
                                'dash-badge-positive': stat.badgeType === 'positive',
                                'dash-badge-warning': stat.badgeType === 'warning',
                                'dash-badge-neutral': stat.badgeType === 'neutral',
                            }"
                        >
                            {{ stat.badge }}
                        </span>
                    </div>
                    <div class="dash-stat-number">{{ stat.value }}</div>
                    <div class="dash-stat-sub">{{ stat.subtitle }}</div>
                </div>
            </div>

            <!-- 2 Kolom Panel Visual: Distribusi Kategori & Aktivitas / Perhatian -->
            <div class="dash-grid dash-2col">
                <!-- Panel 1: Distribusi Aset Berdasarkan Kategori -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <Boxes class="w-4 h-4 text-primary" />
                            Distribusi Kategori Aset
                        </h2>
                        <Link href="/assets" class="dash-link-action">
                            <span>Kelola Aset</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div class="dash-bar-chart">
                        <div v-for="cat in categories" :key="cat.name" class="dash-bar-item">
                            <div class="dash-bar-meta">
                                <span class="dash-bar-label">{{ cat.name }}</span>
                                <span class="dash-bar-count">{{ cat.value }} unit</span>
                            </div>
                            <div class="dash-bar-track">
                                <div
                                    class="dash-bar-fill"
                                    :class="cat.barClass"
                                    :style="{ width: `${cat.percent}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel 2: Peringatan Operasional & Tindakan Segera -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h2 class="dash-card-title">
                            <AlertTriangle class="w-4 h-4 text-amber-500" />
                            Perlu Tindakan & Pemantauan
                        </h2>
                        <Link href="/inventory" class="dash-link-action">
                            <span>Lihat Semua</span>
                            <ArrowUpRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div class="dash-list">
                        <div
                            v-for="item in attentionItems"
                            :key="item.title"
                            class="dash-attention-item"
                        >
                            <div class="dash-attention-info">
                                <span class="dash-attention-title">{{ item.title }}</span>
                                <span class="dash-attention-desc">{{ item.desc }}</span>
                            </div>
                            <span class="dash-badge" :class="item.badgeClass">
                                {{ item.priority }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel 3: Log Aktivitas Mutasi Terkini -->
            <div class="dash-card">
                <div class="dash-card-header">
                    <h2 class="dash-card-title">
                        <Activity class="w-4 h-4 text-primary" />
                        Log Aktivitas Transaksi & Mutasi Terkini
                    </h2>
                    <span class="dash-date-pill">
                        <Sparkles class="w-3.5 h-3.5 text-primary" />
                        Real-time feed
                    </span>
                </div>

                <div class="dash-list">
                    <div
                        v-for="act in activities"
                        :key="act.title"
                        class="dash-list-item"
                    >
                        <div class="dash-list-icon">
                            <component :is="act.icon" class="w-4 h-4" />
                        </div>
                        <div class="dash-list-content">
                            <p class="dash-list-text">{{ act.title }}</p>
                            <span class="dash-list-time">{{ act.time }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style src="@/../css/pages/dashboard.css" scoped></style>