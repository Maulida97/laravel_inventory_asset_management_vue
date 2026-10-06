<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '../../Composables/useTheme';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
});

const { theme, toggleTheme, initTheme } = useTheme();

onMounted(() => {
    initTheme();
});

const form = useForm({
    token: props.token,
    email: props.email || '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Atur Ulang Kata Sandi — AssetFlow Inventory" />

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
                <h1 class="login-title">Atur Ulang Kata Sandi</h1>
                <p class="login-subtitle">Masukkan kata sandi baru untuk akun Anda</p>
            </div>

            <!-- Card -->
            <div class="login-card">
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
                        <label class="form-label" for="email">Alamat Email</label>
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
                            />
                        </div>
                    </div>

                    <!-- New Password Input -->
                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input 
                                :type="showPassword ? 'text' : 'password'" 
                                id="password" 
                                v-model="form.password"
                                class="form-input-custom" 
                                :class="{ 'input-error': form.errors.password }"
                                placeholder="Minimal 8 karakter" 
                                required 
                                autocomplete="new-password"
                                autofocus
                            />
                            <button 
                                type="button" 
                                class="password-toggle" 
                                @click="togglePasswordVisibility" 
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            >
                                <svg v-if="!showPassword" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="input-error-msg">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                        <div class="input-wrapper">
                            <span class="input-icon-left">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input 
                                :type="showPasswordConfirmation ? 'text' : 'password'" 
                                id="password_confirmation" 
                                v-model="form.password_confirmation"
                                class="form-input-custom" 
                                :class="{ 'input-error': form.errors.password_confirmation }"
                                placeholder="Ulangi kata sandi baru" 
                                required 
                                autocomplete="new-password"
                            />
                            <button 
                                type="button" 
                                class="password-toggle" 
                                @click="togglePasswordConfirmationVisibility" 
                                :aria-label="showPasswordConfirmation ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            >
                                <svg v-if="!showPasswordConfirmation" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg v-else width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="input-error-msg">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" :disabled="form.processing">
                        <span>{{ form.processing ? 'Menyimpan Kata Sandi...' : 'Perbarui Kata Sandi' }}</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                Batal mengatur ulang? <Link href="/login">Kembali ke halaman masuk</Link>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/pages/login.css" scoped></style>
