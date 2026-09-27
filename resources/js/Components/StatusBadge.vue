<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    status: {
        type: String,
        default: 'neutral',
    },
    label: {
        type: String,
        default: '',
    },
    pulse: {
        type: Boolean,
        default: false,
    },
    class: {
        type: null,
        default: '',
    },
});

const statusMap = {
    // Success / Available / Active
    success: { bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', dot: 'bg-emerald-500' },
    active: { bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', dot: 'bg-emerald-500' },
    available: { bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', dot: 'bg-emerald-500' },
    approved: { bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20', dot: 'bg-emerald-500' },

    // Warning / Pending / Maintenance
    warning: { bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', dot: 'bg-amber-500' },
    pending: { bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', dot: 'bg-amber-500' },
    maintenance: { bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20', dot: 'bg-amber-500' },

    // Danger / Rejected / Out of Stock / Lost
    danger: { bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20', dot: 'bg-rose-500' },
    rejected: { bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20', dot: 'bg-rose-500' },
    out_of_stock: { bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20', dot: 'bg-rose-500' },
    lost: { bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20', dot: 'bg-rose-500' },

    // Info / In Transit / In Use / Draft
    info: { bg: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20', dot: 'bg-sky-500' },
    in_transit: { bg: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20', dot: 'bg-sky-500' },
    in_use: { bg: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20', dot: 'bg-sky-500' },
    draft: { bg: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20', dot: 'bg-sky-500' },

    // Neutral / Inactive
    neutral: { bg: 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20', dot: 'bg-zinc-400' },
    inactive: { bg: 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20', dot: 'bg-zinc-400' },
};

const currentConfig = computed(() => {
    const key = props.status ? props.status.toLowerCase() : 'neutral';
    return statusMap[key] || statusMap.neutral;
});

const displayLabel = computed(() => {
    if (props.label) return props.label;
    if (!props.status) return '';
    return props.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
});
</script>

<template>
    <span
        :class="cn(
            'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border text-xs font-medium transition-colors',
            currentConfig.bg,
            props.class
        )"
    >
        <span class="relative flex h-1.5 w-1.5">
            <span
                v-if="pulse"
                :class="cn('animate-ping absolute inline-flex h-full w-full rounded-full opacity-75', currentConfig.dot)"
            ></span>
            <span :class="cn('relative inline-flex rounded-full h-1.5 w-1.5', currentConfig.dot)"></span>
        </span>
        <span>{{ displayLabel }}</span>
    </span>
</template>