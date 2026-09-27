<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    appName: {
        type: String,
        default: 'Inventory Asset Management',
    },
    version: {
        type: String,
        default: '1.0.0',
    },
});
</script>

<template>
    <Head title="Welcome" />
    <main class="min-h-screen flex items-center justify-center bg-slate-900 text-white p-6">
        <div class="max-w-md w-full text-center space-y-6 bg-slate-800 p-8 rounded-2xl shadow-xl border border-slate-700">
            <div class="inline-flex p-3 rounded-xl bg-indigo-500/10 text-indigo-400 text-2xl font-bold">
                IAM
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight">{{ appName }}</h1>
            <p class="text-slate-400 text-sm">
                Inertia.js + Vue 3 berhasil terpasang dan berjalan lancar!
            </p>
            <div class="inline-block px-3 py-1 text-xs font-medium rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                Fase 1: F1-06 Completed
            </div>
        </div>
    </main>
</template>