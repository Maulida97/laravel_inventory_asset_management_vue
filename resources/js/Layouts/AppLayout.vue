<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTheme } from '../Composables/useTheme';
import Sidebar from './Components/Sidebar.vue';
import Navbar from './Components/Navbar.vue';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const { initTheme } = useTheme();

const sidebarCollapsed = ref(false);
const mobileSidebarOpen = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    try {
        localStorage.setItem('sidebar_collapsed', String(sidebarCollapsed.value));
    } catch (e) {
        // Ignore localStorage error if disabled
    }
};

const openMobileSidebar = () => {
    mobileSidebarOpen.value = true;
};

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};

onMounted(() => {
    initTheme();
    try {
        const savedState = localStorage.getItem('sidebar_collapsed');
        if (savedState !== null) {
            sidebarCollapsed.value = savedState === 'true';
        }
    } catch (e) {
        // Ignore localStorage error if disabled
    }

    // Auto-close mobile sidebar on page navigation
    router.on('navigate', () => {
        mobileSidebarOpen.value = false;
    });
});
</script>

<template>
    <div class="enterprise-app-shell">
        <!-- Mobile Drawer Overlay -->
        <div
            v-if="mobileSidebarOpen"
            class="mobile-backdrop"
            @click="closeMobileSidebar"
            aria-hidden="true"
        ></div>

        <!-- Sidebar Navigation Component -->
        <Sidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileSidebarOpen"
            @close-mobile="closeMobileSidebar"
            @toggle-collapse="toggleSidebar"
        />

        <!-- Main Viewport Area -->
        <div class="main-viewport-container">
            <!-- Top Navbar Component -->
            <Navbar
                :breadcrumbs="breadcrumbs"
                :sidebar-collapsed="sidebarCollapsed"
                @toggle-sidebar="toggleSidebar"
                @open-mobile-sidebar="openMobileSidebar"
            />

            <!-- Page Header Slot (Optional) -->
            <div v-if="$slots.header" class="page-header-slot">
                <slot name="header" />
            </div>

            <!-- Main Page Content Area -->
            <main class="page-content-wrapper">
                <slot />
            </main>
        </div>
    </div>
</template>

<style src="@/../css/layouts/app-layout.css" scoped></style>