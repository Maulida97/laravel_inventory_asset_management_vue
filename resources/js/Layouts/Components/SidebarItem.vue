<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
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
} from 'lucide-vue-next';

const props = defineProps({
    href: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function, String],
        default: null,
    },
    badge: {
        type: [String, Number],
        default: null,
    },
    badgeVariant: {
        type: String,
        default: 'danger', // danger, warning, primary, secondary
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    children: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const isOpen = ref(false);
const isHovered = ref(false);

const hasChildren = computed(() => props.children && props.children.length > 0);

// Icon mapping dictionary
const iconDictionary = {
    dashboard: LayoutDashboard,
    LayoutDashboard,
    boxes: Boxes,
    Boxes,
    package: Package,
    Package,
    layers: Layers,
    Layers,
    'arrow-down-to-line': ArrowDownToLine,
    ArrowDownToLine,
    'arrow-up-from-line': ArrowUpFromLine,
    ArrowUpFromLine,
    sliders: Sliders,
    Sliders,
    'clipboard-check': ClipboardCheck,
    ClipboardCheck,
    building: Building2,
    Building2,
    qrcode: QrCode,
    QrCode,
    'arrow-left-right': ArrowLeftRight,
    ArrowLeftRight,
    wrench: Wrench,
    Wrench,
    trash: Trash2,
    Trash2,
    'check-square': CheckSquare,
    CheckSquare,
    'bar-chart': BarChart3,
    BarChart3,
    'file-spreadsheet': FileSpreadsheet,
    FileSpreadsheet,
    'file-text': FileText,
    FileText,
    history: History,
    History,
    settings: Settings,
    Settings,
    users: Users,
    Users,
    'map-pin': MapPin,
    MapPin,
    'sliders-horizontal': SlidersHorizontal,
    SlidersHorizontal,
};

const resolveIcon = (iconInput) => {
    if (!iconInput) return null;
    if (typeof iconInput === 'object' || typeof iconInput === 'function') {
        return iconInput;
    }
    if (typeof iconInput === 'string') {
        return iconDictionary[iconInput] || null;
    }
    return null;
};

const resolvedMainIcon = computed(() => resolveIcon(props.icon));

const isExactActive = computed(() => {
    if (!props.href) return false;
    const currentUrl = page.url;
    if (props.href === '/' || props.href === '/dashboard') {
        return currentUrl === '/' || currentUrl === '/dashboard';
    }
    return currentUrl === props.href || currentUrl.startsWith(props.href + '/');
});

const isAnyChildActive = computed(() => {
    if (!hasChildren.value) return false;
    const currentUrl = page.url;
    return props.children.some(
        (child) => child.href && (currentUrl === child.href || currentUrl.startsWith(child.href + '/'))
    );
});

const isActive = computed(() => isExactActive.value || isAnyChildActive.value);

const toggleSubmenu = (e) => {
    if (hasChildren.value) {
        e.preventDefault();
        isOpen.value = !isOpen.value;
    }
};
</script>

<template>
    <div
        class="sidebar-item-group"
        :class="{ 'is-collapsed': collapsed }"
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
    >
        <!-- 1. Direct link with no children -->
        <Link
            v-if="!hasChildren"
            :href="href"
            class="nav-item"
            :class="{ active: isExactActive, 'item-collapsed': collapsed }"
            :title="collapsed ? label : undefined"
        >
            <span class="nav-item-icon">
                <component
                    :is="resolvedMainIcon"
                    v-if="resolvedMainIcon"
                    :size="18"
                    class="icon-svg"
                />
                <slot v-else name="icon" />
            </span>
            <span v-if="!collapsed" class="nav-item-label">{{ label }}</span>
            <span
                v-if="badge !== null && !collapsed"
                class="nav-item-badge"
                :class="`badge-${badgeVariant}`"
            >
                {{ badge }}
            </span>
            <!-- Collapsed badge dot -->
            <span
                v-if="badge !== null && collapsed"
                class="collapsed-badge-dot"
                :class="`dot-${badgeVariant}`"
            ></span>

            <!-- Collapsed Hover Tooltip -->
            <div v-if="collapsed && isHovered" class="collapsed-tooltip">
                {{ label }}
            </div>
        </Link>

        <!-- 2. Parent item with expandable sub-menu -->
        <div v-else class="nav-parent">
            <button
                type="button"
                class="nav-item nav-parent-btn"
                :class="{ active: isActive, 'is-open': isOpen || isAnyChildActive, 'item-collapsed': collapsed }"
                @click="toggleSubmenu"
                :title="collapsed ? label : undefined"
            >
                <span class="nav-item-icon">
                    <component
                        :is="resolvedMainIcon"
                        v-if="resolvedMainIcon"
                        :size="18"
                        class="icon-svg"
                    />
                    <slot v-else name="icon" />
                </span>
                <span v-if="!collapsed" class="nav-item-label">{{ label }}</span>
                <span
                    v-if="badge !== null && !collapsed"
                    class="nav-item-badge"
                    :class="`badge-${badgeVariant}`"
                >
                    {{ badge }}
                </span>
                <ChevronDown
                    v-if="!collapsed"
                    :size="15"
                    class="chevron-icon"
                    :class="{ rotated: isOpen || isAnyChildActive }"
                />
            </button>

            <!-- Collapsed Hover Floating Flyout -->
            <div v-if="collapsed && isHovered && hasChildren" class="collapsed-flyout-menu">
                <div class="flyout-header">{{ label }}</div>
                <div class="flyout-links">
                    <Link
                        v-for="child in children"
                        :key="child.label"
                        :href="child.href"
                        class="flyout-link-item"
                        :class="{ active: page.url === child.href || page.url.startsWith(child.href + '/') }"
                    >
                        <component
                            :is="resolveIcon(child.icon)"
                            v-if="resolveIcon(child.icon)"
                            :size="14"
                            class="child-icon-svg"
                        />
                        <span v-else class="flyout-dot"></span>
                        <span class="flyout-label">{{ child.label }}</span>
                    </Link>
                </div>
            </div>

            <!-- Expanded Submenu items -->
            <div
                v-if="hasChildren && !collapsed && (isOpen || isAnyChildActive)"
                class="nav-submenu"
            >
                <Link
                    v-for="child in children"
                    :key="child.label"
                    :href="child.href"
                    class="submenu-item"
                    :class="{ active: page.url === child.href || page.url.startsWith(child.href + '/') }"
                >
                    <component
                        :is="resolveIcon(child.icon)"
                        v-if="resolveIcon(child.icon)"
                        :size="14"
                        class="child-icon-svg"
                    />
                    <span v-else class="submenu-dot"></span>
                    <span class="submenu-label">{{ child.label }}</span>
                    <span
                        v-if="child.badge"
                        class="nav-item-badge submenu-badge"
                        :class="`badge-${child.badgeVariant || 'secondary'}`"
                    >
                        {{ child.badge }}
                    </span>
                </Link>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/layouts/sidebar-item.css" scoped></style>
