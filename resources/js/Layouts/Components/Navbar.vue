<script setup>
import { onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useTheme } from '../../Composables/useTheme';
import NotificationBell from './NotificationBell.vue';
import UserDropdown from './UserDropdown.vue';
import {
    PanelLeftClose,
    PanelLeftOpen,
    Menu,
    Search,
    Sun,
    Moon,
    ChevronRight,
} from 'lucide-vue-next';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
    sidebarCollapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle-sidebar', 'open-mobile-sidebar']);

const { theme, toggleTheme } = useTheme();

// Global search keyboard shortcut (Ctrl+K / Cmd+K)
const handleKeyDown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('global-search-input');
        if (searchInput) {
            searchInput.focus();
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <header class="app-navbar">
        <div class="navbar-left">
            <!-- Sidebar toggle button (desktop) -->
            <button
                type="button"
                class="icon-btn desktop-toggle"
                @click="emit('toggle-sidebar')"
                aria-label="Toggle sidebar"
                :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
            >
                <PanelLeftOpen v-if="sidebarCollapsed" class="w-5 h-5" />
                <PanelLeftClose v-else class="w-5 h-5" />
            </button>

            <!-- Sidebar toggle button (mobile) -->
            <button
                type="button"
                class="icon-btn mobile-toggle"
                @click="emit('open-mobile-sidebar')"
                aria-label="Open navigation drawer"
            >
                <Menu class="w-5 h-5" />
            </button>

            <!-- Dynamic Breadcrumbs -->
            <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="breadcrumbs-nav" aria-label="Breadcrumb">
                <ol class="breadcrumbs-list">
                    <li v-for="(crumb, idx) in breadcrumbs" :key="crumb.label" class="breadcrumb-item">
                        <Link v-if="crumb.href && idx !== breadcrumbs.length - 1" :href="crumb.href" class="breadcrumb-link">
                            {{ crumb.label }}
                        </Link>
                        <span v-else class="breadcrumb-current" :aria-current="idx === breadcrumbs.length - 1 ? 'page' : undefined">
                            {{ crumb.label }}
                        </span>
                        <ChevronRight
                            v-if="idx < breadcrumbs.length - 1"
                            class="breadcrumb-separator"
                        />
                    </li>
                </ol>
            </nav>
            <div v-else class="navbar-default-title">
                <span class="app-title-badge">Inventra System</span>
            </div>
        </div>

        <div class="navbar-right">
            <!-- Global quick search bar -->
            <div class="global-search-container">
                <Search class="search-icon w-4 h-4" />
                <input
                    id="global-search-input"
                    type="text"
                    placeholder="Pencarian cepat (Ctrl+K)..."
                    class="search-input"
                />
                <kbd class="search-shortcut">Ctrl K</kbd>
            </div>

            <!-- Theme Toggle -->
            <button
                type="button"
                class="icon-btn theme-toggle-btn"
                @click="toggleTheme"
                :title="theme === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'"
                aria-label="Toggle theme"
            >
                <Sun v-if="theme === 'dark'" class="w-4 h-4 text-amber-400" />
                <Moon v-else class="w-4 h-4 text-slate-600" />
            </button>

            <!-- Notifications Bell -->
            <NotificationBell />

            <!-- User Dropdown Menu -->
            <UserDropdown />
        </div>
    </header>
</template>

<style src="@/../css/layouts/navbar.css" scoped></style>
