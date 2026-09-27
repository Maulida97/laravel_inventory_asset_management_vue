<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useTheme } from '../Composables/useTheme';

const { theme, toggleTheme, initTheme } = useTheme();
const isSidebarOpen = ref(false);
const isNotificationOpen = ref(false);
const isUserMenuOpen = ref(false);

const notifications = ref([
    {
        id: 1,
        type: 'warn',
        title: 'Maintenance Overdue',
        message: 'Toyota Hilux #AST-003 has overdue maintenance scheduled for Sep 20',
        time: '25 minutes ago',
        unread: true,
    },
    {
        id: 2,
        type: 'info',
        title: 'Asset Assigned',
        message: 'MacBook Pro #AST-005 has been assigned to Emily Davis (Engineering)',
        time: '1 hour ago',
        unread: true,
    },
    {
        id: 3,
        type: 'danger',
        title: 'Low Stock Alert',
        message: 'Printer Toner Cartridges below minimum threshold (3 remaining)',
        time: '2 hours ago',
        unread: true,
    },
    {
        id: 4,
        type: 'success',
        title: 'Work Order Completed',
        message: 'WO-018 HVAC inspection has been completed by TechServ Solutions',
        time: 'Yesterday',
        unread: false,
    },
    {
        id: 5,
        type: 'info',
        title: 'Report Ready',
        message: 'Monthly Asset Inventory Report for September is ready for download',
        time: 'Yesterday',
        unread: false,
    },
]);

const unreadCount = computed(() => {
    return notifications.value.filter(n => n.unread).length;
});

const toggleNotification = (e) => {
    e.stopPropagation();
    isNotificationOpen.value = !isNotificationOpen.value;
    isUserMenuOpen.value = false;
};

const toggleUserMenu = (e) => {
    e.stopPropagation();
    isUserMenuOpen.value = !isUserMenuOpen.value;
    isNotificationOpen.value = false;
};

const markAllAsRead = () => {
    notifications.value.forEach(n => n.unread = false);
};

const markAsRead = (notif) => {
    notif.unread = false;
};

const handleClickOutside = (e) => {
    if (!e.target.closest('.notification-wrapper')) {
        isNotificationOpen.value = false;
    }
    if (!e.target.closest('.user-menu-wrapper')) {
        isUserMenuOpen.value = false;
    }
};

onMounted(() => {
    initTheme();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};
</script>

<template>
    <div class="app-shell">
        <!-- Mobile Overlay -->
        <div 
            class="sidebar-overlay" 
            :class="{ active: isSidebarOpen }" 
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside class="sidebar" :class="{ open: isSidebarOpen }">
            <div class="sidebar-header">
                <Link href="/" class="sidebar-logo">
                    <div class="logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <span class="logo-text">AssetFlow</span>
                </Link>
                <button class="sidebar-close" @click="closeSidebar" aria-label="Close sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">Main</span>
                    <Link href="/" class="nav-item" :class="{ active: $page.url === '/' || $page.url === '/dashboard' }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                        </svg>
                        <span>Dashboard</span>
                    </Link>
                    <Link href="#/assets" class="nav-item" :class="{ active: $page.url.startsWith('/assets') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        </svg>
                        <span>Assets</span>
                    </Link>
                    <Link href="#/maintenance" class="nav-item" :class="{ active: $page.url.startsWith('/maintenance') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                        <span>Maintenance</span>
                    </Link>
                    <Link href="#/check-in-out" class="nav-item" :class="{ active: $page.url.startsWith('/check-in-out') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>
                        </svg>
                        <span>Check In/Out</span>
                    </Link>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">Organization</span>
                    <Link href="#/categories" class="nav-item" :class="{ active: $page.url.startsWith('/categories') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>Categories</span>
                    </Link>
                    <Link href="#/reports" class="nav-item" :class="{ active: $page.url.startsWith('/reports') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                        </svg>
                        <span>Reports</span>
                    </Link>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">System</span>
                    <Link href="#/settings" class="nav-item" :class="{ active: $page.url.startsWith('/settings') }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                        <span>Settings</span>
                    </Link>
                </div>
            </nav>

            <div class="sidebar-footer">
                <Link href="/login" class="sidebar-user">
                    <div class="user-avatar">JS</div>
                    <div class="user-info">
                        <span class="user-name">John Smith</span>
                        <span class="user-role">Administrator</span>
                    </div>
                </Link>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="main-wrapper">
            <!-- Topbar -->
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" @click="toggleSidebar" aria-label="Toggle menu">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div class="search-bar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" placeholder="Search assets, work orders, reports..." class="search-input" />
                    </div>
                </div>
                <div class="topbar-right">
                    <!-- Theme Toggle -->
                    <button class="topbar-btn" @click="toggleTheme" :title="theme === 'dark' ? 'Mode Terang' : 'Mode Gelap'">
                        <svg v-if="theme === 'dark'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        </svg>
                        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>

                    <!-- Notifications Dropdown Wrapper -->
                    <div class="notification-wrapper">
                        <button 
                            class="topbar-btn notification-btn" 
                            @click="toggleNotification" 
                            aria-label="Notifications" 
                            title="Notifications"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            </svg>
                            <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
                        </button>

                        <!-- Notification Panel Dropdown -->
                        <div v-if="isNotificationOpen" class="notif-panel">
                            <div class="notif-panel-header">
                                <h3>Notifications</h3>
                                <button v-if="unreadCount > 0" class="notif-mark-read" @click="markAllAsRead">
                                    Mark all as read
                                </button>
                            </div>

                            <div class="notif-list">
                                <div 
                                    v-for="notif in notifications" 
                                    :key="notif.id" 
                                    class="notif-item" 
                                    :class="{ unread: notif.unread }"
                                    @click="markAsRead(notif)"
                                >
                                    <div class="notif-icon" :class="notif.type">
                                        <!-- Warning Icon -->
                                        <svg v-if="notif.type === 'warn'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                        <!-- Danger Icon -->
                                        <svg v-else-if="notif.type === 'danger'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                        <!-- Success Icon -->
                                        <svg v-else-if="notif.type === 'success'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        <!-- Info Icon -->
                                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                                        </svg>
                                    </div>
                                    <div class="notif-body">
                                        <p><strong>{{ notif.title }}</strong> — {{ notif.message }}</p>
                                        <div class="notif-time">{{ notif.time }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="notif-panel-footer">
                                <Link href="#/settings" @click="isNotificationOpen = false">View all notifications</Link>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu Dropdown Wrapper -->
                    <div class="user-menu-wrapper" style="position: relative;">
                        <button class="topbar-user" @click="toggleUserMenu" style="background: none; border: none; padding: 0;">
                            <div class="user-avatar small">JS</div>
                        </button>

                        <div v-if="isUserMenuOpen" class="notif-panel" style="width: 220px; right: 0;">
                            <div style="padding: 14px 16px; border-bottom: 1px solid var(--border);">
                                <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-primary);">John Smith</div>
                                <div style="font-size: 0.75rem; color: var(--text-tertiary);">admin@assetflow.io</div>
                            </div>
                            <div style="padding: 6px;">
                                <Link href="#/settings" class="nav-item" style="padding: 8px 12px; margin-bottom: 2px;" @click="isUserMenuOpen = false">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                    </svg>
                                    <span>Pengaturan Akun</span>
                                </Link>
                                <Link href="/login" class="nav-item" style="padding: 8px 12px; color: var(--danger);" @click="isUserMenuOpen = false">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    <span>Keluar (Logout)</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="page-content">
                <slot />
            </main>
        </div>
    </div>
</template>