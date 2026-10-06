<script setup>
import { onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '../../Composables/useTheme';

defineProps({
    status: {
        type: String,
        default: null,
    },
});

const { theme, toggleTheme, initTheme } = useTheme();

onMounted(() => {
    initTheme();
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>

<template>
    <Head title="Lupa Kata Sandi — AssetFlow Inventory" />

    <div class="login-wrapper">
        <div class="ambient-glow"></div>

        <!-- Theme Toggle Floating Button -->
        <button 
            type="button" 
            class="theme-switcher-float" 
            @click="toggleTheme" 
            :aria-label="theme === 'dark' ? 'Mode Terang' : 'Mode Gelap'"
        >
            <svg v-if="theme === 'dark'" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg v-else width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <span>{{ theme === 'dark' ? 'Mode Terang' : 'Mode Gelap' }}</span>
        </button>

        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <Link href="/" class="login-logo">
                    <div class="login-logo-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                    </div>
                    <span class="login-logo-text">AssetFlow</span>
                </Link>
                <h1 class="login-title">Lupa Kata Sandi?</h1>
                <p class="login-subtitle">Masukkan alamat email Anda untuk menerima tautan pemulihan kata sandi</p>
            </div>

            <!-- Card -->
            <div class="login-card">
                <!-- Status Banner -->
                <div v-if="status" class="alert-success-banner">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    <span>{{ status }}</span>
                </div>

                <!-- Error Banner -->
                <div v-if="form.errors.email" class="alert-error-banner">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ form.errors.email }}</span>
                </div>

                <form @submit.prevent="submit">
                    <!-- Email Input -->
                    <div class="form-group">
                        <label class="form-label" for="email">Alamat Email Terdaftar</label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input 
                                type="email" 
                                id="email" 
                                v-model="form.email"
                                class="form-input-custom" 
                                :class="{ 'input-error': form.errors.email }"
                                placeholder="nama@perusahaan.com" 
                                required 
                                autocomplete="email"
                                autofocus
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" :disabled="form.processing">
                        <span>{{ form.processing ? 'Mengirim Tautan...' : 'Kirim Tautan Reset' }}</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                Ingat kata sandi Anda? <Link href="/login">Kembali ke halaman masuk</Link>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/pages/login.css" scoped></style>
