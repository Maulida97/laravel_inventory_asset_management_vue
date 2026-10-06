<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTheme } from '../../Composables/useTheme';

const props = defineProps({
    departments: {
        type: Array,
        default: () => [],
    },
});

const { theme, toggleTheme, initTheme } = useTheme();

onMounted(() => {
    initTheme();
});

const form = useForm({
    name: '',
    email: '',
    employee_id: '',
    department_id: '',
    position: '',
    phone_number: '',
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

const handleRegister = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Daftar Akun Baru — AssetFlow Inventory" />

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

        <div class="register-container">
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
                <h1 class="login-title">Pendaftaran Karyawan Baru</h1>
                <p class="login-subtitle">Lengkapi formulir untuk mengajukan akun sistem AssetFlow</p>
            </div>

            <!-- Register Card -->
            <div class="login-card">
                <!-- Info Notice Banner -->
                <div class="alert-success-banner" style="margin-bottom: 24px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <span>Akun baru akan diverifikasi dan disetujui oleh Super Admin sebelum dapat digunakan.</span>
                </div>

                <form @submit.prevent="handleRegister">
                    <!-- Row 1: Nama Lengkap & Email -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="name">
                                Nama Lengkap <span class="text-required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="name" 
                                    v-model="form.name"
                                    class="form-input-custom" 
                                    :class="{ 'input-error': form.errors.name }"
                                    placeholder="Contoh: Budi Santoso" 
                                    required 
                                    autocomplete="name"
                                    autofocus
                                />
                            </div>
                            <p v-if="form.errors.name" class="input-error-msg">{{ form.errors.name }}</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">
                                Email Korporat <span class="text-required">*</span>
                            </label>
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
                                    placeholder="budi@perusahaan.com" 
                                    required 
                                    autocomplete="email"
                                />
                            </div>
                            <p v-if="form.errors.email" class="input-error-msg">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <!-- Row 2: NIP & Departemen -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="employee_id">
                                NIP / Nomor Karyawan
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                                        <line x1="7" y1="8" x2="17" y2="8"/>
                                        <line x1="7" y1="12" x2="13" y2="12"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="employee_id" 
                                    v-model="form.employee_id"
                                    class="form-input-custom" 
                                    :class="{ 'input-error': form.errors.employee_id }"
                                    placeholder="EMP-01234" 
                                    autocomplete="off"
                                />
                            </div>
                            <p v-if="form.errors.employee_id" class="input-error-msg">{{ form.errors.employee_id }}</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="department_id">
                                Departemen <span class="text-required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M3 21h18"/>
                                        <path d="M9 8h1"/>
                                        <path d="M9 12h1"/>
                                        <path d="M9 16h1"/>
                                        <path d="M14 8h1"/>
                                        <path d="M14 12h1"/>
                                        <path d="M14 16h1"/>
                                        <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
                                    </svg>
                                </span>
                                <select 
                                    id="department_id" 
                                    v-model="form.department_id"
                                    class="form-input-custom form-select-custom" 
                                    :class="{ 'input-error': form.errors.department_id }"
                                    required
                                >
                                    <option value="" disabled>Pilih Departemen...</option>
                                    <option 
                                        v-for="dept in departments" 
                                        :key="dept.id" 
                                        :value="dept.id"
                                    >
                                        {{ dept.name }} ({{ dept.code }})
                                    </option>
                                </select>
                            </div>
                            <p v-if="form.errors.department_id" class="input-error-msg">{{ form.errors.department_id }}</p>
                        </div>
                    </div>

                    <!-- Row 3: Posisi / Jabatan & Nomor Telepon -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="position">
                                Posisi / Jabatan
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="position" 
                                    v-model="form.position"
                                    class="form-input-custom" 
                                    :class="{ 'input-error': form.errors.position }"
                                    placeholder="Contoh: Staff IT" 
                                    autocomplete="organization-title"
                                />
                            </div>
                            <p v-if="form.errors.position" class="input-error-msg">{{ form.errors.position }}</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone_number">
                                Nomor Telepon / WhatsApp
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                </span>
                                <input 
                                    type="tel" 
                                    id="phone_number" 
                                    v-model="form.phone_number"
                                    class="form-input-custom" 
                                    :class="{ 'input-error': form.errors.phone_number }"
                                    placeholder="0812xxxxxxxx" 
                                    autocomplete="tel"
                                />
                            </div>
                            <p v-if="form.errors.phone_number" class="input-error-msg">{{ form.errors.phone_number }}</p>
                        </div>
                    </div>

                    <!-- Row 4: Kata Sandi & Konfirmasi Kata Sandi -->
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="password">
                                Kata Sandi <span class="text-required">*</span>
                            </label>
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
                                    placeholder="Min. 8 karakter" 
                                    required 
                                    autocomplete="new-password"
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

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">
                                Konfirmasi Kata Sandi <span class="text-required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <span class="input-icon-left">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    </svg>
                                </span>
                                <input 
                                    :type="showPasswordConfirmation ? 'text' : 'password'" 
                                    id="password_confirmation" 
                                    v-model="form.password_confirmation"
                                    class="form-input-custom" 
                                    :class="{ 'input-error': form.errors.password_confirmation }"
                                    placeholder="Ulangi kata sandi" 
                                    required 
                                    autocomplete="new-password"
                                />
                                <button 
                                    type="button" 
                                    class="password-toggle" 
                                    @click="togglePasswordConfirmationVisibility" 
                                    :aria-label="showPasswordConfirmation ? 'Sembunyikan konfirmasi kata sandi' : 'Tampilkan konfirmasi kata sandi'"
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
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" :disabled="form.processing" style="margin-top: 12px;">
                        <span>{{ form.processing ? 'Mengirim Pendaftaran...' : 'Daftar Akun Baru' }}</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                Sudah memiliki akun? <Link href="/login">Masuk ke sistem</Link>
            </div>
        </div>
    </div>
</template>

<style src="@/../css/pages/login.css" scoped></style>
