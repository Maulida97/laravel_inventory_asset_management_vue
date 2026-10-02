<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Bell, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next';

const isOpen = ref(false);
const bellRef = ref(null);

const notifications = ref([
    {
        id: 1,
        type: 'warning',
        title: 'Pemeliharaan Jatuh Tempo',
        message: 'Aset Toyota Hilux #AST-003 memiliki jadwal servis berkala.',
        time: '25 menit yang lalu',
        unread: true,
    },
    {
        id: 2,
        type: 'danger',
        title: 'Peringatan Stok Menipis',
        message: 'Item Printer Toner Cartridges berada di bawah batas minimum (sisa 3 unit).',
        time: '2 jam yang lalu',
        unread: true,
    },
    {
        id: 3,
        type: 'info',
        title: 'Tugas Approval Baru',
        message: 'Permintaan Barang #REQ-2026-0012 menunggu persetujuan Anda.',
        time: '3 jam yang lalu',
        unread: true,
    },
]);

const unreadCount = computed(() => {
    return notifications.value.filter((n) => n.unread).length;
});

const toggleDropdown = (e) => {
    e.stopPropagation();
    isOpen.value = !isOpen.value;
};

const markAllAsRead = () => {
    notifications.value.forEach((n) => (n.unread = false));
};

const markAsRead = (item) => {
    item.unread = false;
};

const handleClickOutside = (e) => {
    if (bellRef.value && !bellRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="bellRef" class="notification-bell-container">
        <!-- Bell Trigger Button -->
        <button
            type="button"
            class="bell-btn"
            @click="toggleDropdown"
            aria-label="Notifications"
            :title="`Notifikasi (${unreadCount} belum dibaca)`"
        >
            <Bell class="w-4 h-4" />
            <span v-if="unreadCount > 0" class="bell-badge">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Notification Popover -->
        <div v-if="isOpen" class="notification-popover">
            <div class="popover-header">
                <div class="header-left">
                    <span class="header-title">Notifikasi</span>
                    <span v-if="unreadCount > 0" class="header-count">{{ unreadCount }} baru</span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="mark-all-read-btn"
                    @click="markAllAsRead"
                >
                    Tandai dibaca
                </button>
            </div>

            <div class="popover-body">
                <div
                    v-for="item in notifications"
                    :key="item.id"
                    class="notification-item"
                    :class="{ unread: item.unread }"
                    @click="markAsRead(item)"
                >
                    <div class="item-icon-wrapper" :class="`icon-${item.type}`">
                        <AlertCircle v-if="item.type === 'danger'" class="w-3.5 h-3.5" />
                        <AlertTriangle v-else-if="item.type === 'warning'" class="w-3.5 h-3.5" />
                        <Info v-else class="w-3.5 h-3.5" />
                    </div>

                    <div class="item-content">
                        <div class="item-title-row">
                            <span class="item-title">{{ item.title }}</span>
                            <span class="item-time">{{ item.time }}</span>
                        </div>
                        <p class="item-desc">{{ item.message }}</p>
                    </div>

                    <div v-if="item.unread" class="unread-dot"></div>
                </div>

                <div v-if="notifications.length === 0" class="empty-notifications">
                    <p>Tidak ada notifikasi baru</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/layouts/notification-bell.css" scoped></style>
