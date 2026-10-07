<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import SidebarItem from './SidebarItem.vue';
import { usePermission } from '../../Composables/usePermission';
import {
    LayoutDashboard,
    Boxes,
    Package,
    Layers,
    ArrowDownToLine,
    ArrowUpFromLine,
    Sliders,
    ClipboardCheck,
    Building2,
    QrCode,
    ArrowLeftRight,
    Wrench,
    Trash2,
    CheckSquare,
    BarChart3,
    FileSpreadsheet,
    FileText,
    History,
    Settings,
    Users,
    MapPin,
    SlidersHorizontal,
    UserCheck,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
    mobileOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close-mobile', 'toggle-collapse']);

const page = usePage();
const { can, hasAnyRole, isSuperAdmin } = usePermission();

const user = computed(() => page.props.auth?.user);

const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});

const userRole = computed(() => {
    if (user.value?.roles && user.value.roles.length > 0) {
        return user.value.roles[0].replace(/[-_]/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
    }
    return user.value?.position || 'Staff';
});

// Permission helpers for menu sections
const canAccessStock = computed(() => {
    return isSuperAdmin.value || can('inventory.view') || hasAnyRole(['Warehouse Admin', 'Manager']);
});

const canAccessAssets = computed(() => {
    return isSuperAdmin.value || can('asset.view') || hasAnyRole(['Asset Admin', 'Manager']);
});

const canAccessApprovals = computed(() => {
    return isSuperAdmin.value || can('approval.view') || hasAnyRole(['Super Admin', 'Manager', 'Department Head']);
});

const canAccessReports = computed(() => {
    return isSuperAdmin.value || can('report.inventory.view') || can('report.asset.view') || hasAnyRole(['Manager']);
});

const canAccessAdmin = computed(() => {
    return isSuperAdmin.value || can('user.view') || can('department.manage') || can('location.view');
});
</script>

<template>
    <aside
        class="app-sidebar"
        :class="{
            'is-collapsed': collapsed,
            'mobile-open': mobileOpen,
        }"
    >
        <!-- Header / Logo Brand -->
        <div class="sidebar-header">
            <Link href="/" class="sidebar-brand">
                <div class="brand-logo-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                        <line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </div>
                <div v-if="!collapsed" class="brand-info">
                    <span class="brand-name">AssetFlow</span>
                    <span class="brand-tag">Enterprise</span>
                </div>
            </Link>

            <!-- Mobile close button -->
            <button
                type="button"
                class="sidebar-mobile-close"
                @click="emit('close-mobile')"
                aria-label="Close menu"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Navigation Groups -->
        <nav class="sidebar-scrollable-nav">
            <!-- 1. UTAMA -->
            <div class="nav-section">
                <span v-if="!collapsed" class="section-heading">Utama</span>
                <SidebarItem
                    href="/"
                    label="Dashboard"
                    :icon="LayoutDashboard"
                    :collapsed="collapsed"
                />
            </div>

            <!-- 2. MANAJEMEN STOK -->
            <div v-if="canAccessStock" class="nav-section">
                <span v-if="!collapsed" class="section-heading">Manajemen Stok</span>
                <SidebarItem
                    label="Inventaris"
                    :icon="Package"
                    :collapsed="collapsed"
                    :children="[
                        { label: 'Item Master', href: '/inventory/items', icon: Package },
                        { label: 'Ledger Mutasi', href: '/inventory/ledger', icon: Layers },
                        { label: 'Stok Masuk', href: '/inventory/stock-in', icon: ArrowDownToLine },
                        { label: 'Stok Keluar', href: '/inventory/stock-out', icon: ArrowUpFromLine },
                        { label: 'Penyesuaian', href: '/inventory/adjustments', icon: Sliders },
                        { label: 'Stock Opname', href: '/inventory/opnames', icon: ClipboardCheck },
                    ]"
                />
            </div>

            <!-- 3. MANAJEMEN ASET -->
            <div v-if="canAccessAssets" class="nav-section">
                <span v-if="!collapsed" class="section-heading">Manajemen Aset</span>
                <SidebarItem
                    label="Aset Tetap"
                    :icon="Building2"
                    :collapsed="collapsed"
                    :children="[
                        { label: 'Register Aset', href: '/assets/items', icon: QrCode },
                        { label: 'Serah Terima (Check In/Out)', href: '/assets/check-in-out', icon: ArrowLeftRight },
                        { label: 'Pemeliharaan', href: '/assets/maintenances', icon: Wrench },
                        { label: 'Pelepasan (Disposal)', href: '/assets/disposals', icon: Trash2 },
                    ]"
                />
            </div>

            <!-- 4. ALUR KERJA / APPROVAL -->
            <div v-if="canAccessApprovals" class="nav-section">
                <span v-if="!collapsed" class="section-heading">Alur Kerja</span>
                <SidebarItem
                    href="/approvals"
                    label="Inbox Approval"
                    :icon="CheckSquare"
                    :collapsed="collapsed"
                    :badge="null"
                    badge-variant="warning"
                />
            </div>

            <!-- 5. WAWASAN & LAPORAN -->
            <div v-if="canAccessReports" class="nav-section">
                <span v-if="!collapsed" class="section-heading">Wawasan</span>
                <SidebarItem
                    label="Laporan"
                    :icon="BarChart3"
                    :collapsed="collapsed"
                    :children="[
                        { label: 'Laporan Inventaris', href: '/reports/inventory', icon: FileSpreadsheet },
                        { label: 'Laporan Aset', href: '/reports/assets', icon: FileText },
                        { label: 'Audit Trail', href: '/reports/audit-logs', icon: History },
                    ]"
                />
            </div>

            <!-- 6. ADMINISTRASI -->
            <div v-if="canAccessAdmin" class="nav-section">
                <span v-if="!collapsed" class="section-heading">Administrasi</span>
                <SidebarItem
                    label="Pengaturan"
                    :icon="Settings"
                    :collapsed="collapsed"
                    :children="[
                        { label: 'Persetujuan Registrasi', href: '/settings/user-registrations', icon: UserCheck },
                        { label: 'Manajemen Pengguna', href: '/settings/users', icon: Users },
                        { label: 'Manajemen Lokasi', href: '/locations', icon: MapPin },
                        { label: 'Departemen', href: '/settings/departments', icon: Building2 },
                        { label: 'Pengaturan Sistem', href: '/settings/system', icon: SlidersHorizontal },
                    ]"
                />
            </div>
        </nav>

        <!-- Footer User Profile card -->
        <div class="sidebar-footer">
            <div class="user-card" :class="{ 'is-collapsed': collapsed }">
                <div class="user-avatar-circle">
                    {{ userInitials }}
                </div>
                <div v-if="!collapsed" class="user-meta">
                    <span class="user-name" :title="user?.name">{{ user?.name || 'User' }}</span>
                    <span class="user-role-badge">{{ userRole }}</span>
                </div>
            </div>
        </div>
    </aside>
</template>

<style src="@/../css/layouts/sidebar.css" scoped></style>
