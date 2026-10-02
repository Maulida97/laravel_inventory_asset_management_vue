<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { ChevronDown, User, LogOut } from 'lucide-vue-next';

const isOpen = ref(false);
const dropdownRef = ref(null);

const page = usePage();
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
    return user.value?.position || 'Pengguna';
});

const toggleDropdown = (e) => {
    e.stopPropagation();
    isOpen.value = !isOpen.value;
};

const handleLogout = () => {
    isOpen.value = false;
    router.post('/logout');
};

const handleClickOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
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
    <div ref="dropdownRef" class="user-dropdown-container">
        <!-- Trigger button -->
        <button
            type="button"
            class="user-trigger-btn"
            @click="toggleDropdown"
            aria-label="User menu"
        >
            <div class="user-avatar">
                {{ userInitials }}
            </div>
            <div class="user-details-compact">
                <span class="user-display-name">{{ user?.name || 'User' }}</span>
                <span class="user-display-role">{{ userRole }}</span>
            </div>
            <ChevronDown
                class="chevron-icon"
                :class="{ rotated: isOpen }"
            />
        </button>

        <!-- Dropdown menu popover -->
        <div v-if="isOpen" class="user-menu-popover">
            <div class="user-menu-header">
                <div class="header-avatar">{{ userInitials }}</div>
                <div class="header-info">
                    <span class="header-name">{{ user?.name || 'User' }}</span>
                    <span class="header-email">{{ user?.email }}</span>
                    <span class="header-role-badge">{{ userRole }}</span>
                </div>
            </div>

            <div class="user-menu-items">
                <Link href="/settings/users" class="menu-action-item" @click="isOpen = false">
                    <User class="w-4 h-4 menu-icon" />
                    <span>Profil Pengguna</span>
                </Link>

                <div class="menu-divider"></div>

                <button type="button" class="menu-action-item logout-btn" @click="handleLogout">
                    <LogOut class="w-4 h-4 logout-icon" />
                    <span>Keluar (Logout)</span>
                </button>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/layouts/user-dropdown.css" scoped></style>
