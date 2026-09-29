<template>
    <div class="profile-page">

        <!-- Header -->
        <div class="page-header">
            <div>
                <span class="label">ACCOUNT</span>
                <h1>Admin Profile</h1>
                <p>Manage your personal information and account details.</p>
            </div>

            <div class="header-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Administrator</span>
            </div>
        </div>


        <!-- Alerts -->
        <transition name="fade">
            <div
                v-if="success"
                class="alert alert-success"
            >
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ success }}</span>
            </div>
        </transition>

        <transition name="fade">
            <div
                v-if="error"
                class="alert alert-error"
            >
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ error }}</span>
            </div>
        </transition>


        <!-- Profile Card -->
        <div class="profile-card">

            <!-- Avatar Section -->
            <div class="avatar-section">
                <div class="avatar">
                    {{ initials }}
                </div>

                <div class="avatar-info">
                    <h3>{{ form.name || 'Administrator' }}</h3>
                    <p>{{ form.email || 'No email' }}</p>
                    <span class="role-tag">Admin Account</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="divider"></div>

            <!-- Form -->
            <form
                class="profile-form"
                @submit.prevent="updateProfile"
            >
                <h4 class="form-title">Personal Information</h4>

                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">
                        Full Name
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>

                        <input
                            v-model="form.name"
                            type="text"
                            class="form-control"
                            placeholder="Enter your full name"
                            required
                        />
                    </div>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">
                        Email Address
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon"></i>

                        <input
                            v-model="form.email"
                            type="email"
                            class="form-control"
                            placeholder="Enter your email"
                            required
                        />
                    </div>
                </div>

                <!-- Role (readonly) -->
                <div class="form-group">
                    <label class="form-label">
                        Role
                    </label>

                    <div class="input-wrapper">
                        <i class="fa-solid fa-user-tag input-icon"></i>

                        <input
                            type="text"
                            class="form-control readonly"
                            value="Administrator"
                            readonly
                        />
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button
                        type="submit"
                        class="btn-primary"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="spinner"></span>

                        <i
                            v-else
                            class="fa-solid fa-floppy-disk"
                        ></i>

                        {{ loading ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</template>


<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import {
    getAdminProfile,
    updateAdminProfile
} from '../../services/admin';

const loading = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
    name: '',
    email: '',
});

const initials = computed(() => {
    const name = form.name || 'A';
    const parts = name.trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }

    return (
        parts[0][0] + parts[parts.length - 1][0]
    ).toUpperCase();
});

async function loadProfile() {
    loading.value = true;
    error.value = '';

    try {
        const response = await getAdminProfile();

        const data =
            response.data?.user ??
            response.data?.data ??
            response.data;

        form.name = data?.name ?? '';
        form.email = data?.email ?? '';

    } catch (err) {
        console.error('Load admin profile error:', err);

        error.value =
            err.response?.data?.message ||
            'Unable to load profile.';
    } finally {
        loading.value = false;
    }
}

async function updateProfile() {
    loading.value = true;
    error.value = '';
    success.value = '';

    try {
        const response = await updateAdminProfile({
            name: form.name,
            email: form.email,
        });

        success.value =
            response.data?.message ||
            'Profile updated successfully.';

        setTimeout(() => {
            success.value = '';
        }, 3000);

    } catch (err) {
        console.error('Update admin profile error:', err);

        error.value =
            err.response?.data?.message ||
            'Unable to update profile.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadProfile();
});
</script>


<style scoped>

/* =========================================================
   PAGE
========================================================= */

.profile-page {
    width: 100%;
    max-width: 900px;
    margin: 0 auto;
    padding: 24px;
    box-sizing: border-box;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 28px;
}

.label {
    display: inline-block;
    margin-bottom: 6px;
    color: #6366f1;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
}

.page-header h1 {
    margin: 0;
    color: #111827;
    font-size: 28px;
    font-weight: 700;
}

.page-header p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border: 1px solid #c7d2fe;
    border-radius: 10px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.header-badge i {
    font-size: 13px;
}


/* =========================================================
   ALERTS
========================================================= */

.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    margin-bottom: 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
}

.alert-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #047857;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

.alert i {
    font-size: 15px;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}


/* =========================================================
   CARD
========================================================= */

.profile-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}


/* =========================================================
   AVATAR SECTION
========================================================= */

.avatar-section {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 26px;
}

.avatar {
    width: 84px;
    height: 84px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #06b6d4);
    color: #ffffff;
    font-size: 30px;
    font-weight: 800;
    box-shadow: 0 10px 24px rgba(99, 102, 241, 0.30);
}

.avatar-info h3 {
    margin: 0 0 5px;
    color: #111827;
    font-size: 20px;
    font-weight: 700;
}

.avatar-info p {
    margin: 0 0 8px;
    color: #6b7280;
    font-size: 14px;
}

.role-tag {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
}


/* =========================================================
   DIVIDER
========================================================= */

.divider {
    height: 1px;
    background: #f1f5f9;
    margin-bottom: 26px;
}


/* =========================================================
   FORM
========================================================= */

.form-title {
    margin: 0 0 20px;
    color: #111827;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
}


/* =========================================================
   INPUT
========================================================= */

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    top: 50%;
    left: 16px;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 14px;
    pointer-events: none;
    transition: color 0.2s ease;
}

.form-control {
    width: 100%;
    height: 48px;
    padding: 0 16px 0 44px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control::placeholder {
    color: #9ca3af;
}

.form-control:hover {
    border-color: #9ca3af;
}

.form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.form-control:focus + .input-icon,
.input-wrapper:focus-within .input-icon {
    color: #6366f1;
}

.form-control.readonly {
    background: #f9fafb;
    color: #6b7280;
    cursor: not-allowed;
}


/* =========================================================
   ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 26px;
    padding-top: 22px;
    border-top: 1px solid #f1f5f9;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 44px;
    padding: 0 24px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    color: #ffffff;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
}

.btn-primary:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-primary i {
    font-size: 13px;
}


/* =========================================================
   SPINNER
========================================================= */

.spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}


/* =========================================================
   DARK MODE
========================================================= */

:global(html.dark) .profile-page {
    background: #0b0f17;
}

:global(html.dark) .page-header h1 {
    color: #f9fafb;
}

:global(html.dark) .page-header p {
    color: #94a3b8;
}

:global(html.dark) .header-badge {
    background: rgba(99, 102, 241, 0.15);
    border-color: rgba(99, 102, 241, 0.35);
    color: #a5b4fc;
}

:global(html.dark) .profile-card {
    background: #111827;
    border-color: #1f2937;
    box-shadow: none;
}

:global(html.dark) .avatar-info h3 {
    color: #f9fafb;
}

:global(html.dark) .avatar-info p {
    color: #94a3b8;
}

:global(html.dark) .role-tag {
    background: rgba(99, 102, 241, 0.18);
    color: #a5b4fc;
}

:global(html.dark) .divider {
    background: #1f2937;
}

:global(html.dark) .form-title {
    color: #f9fafb;
}

:global(html.dark) .form-label {
    color: #cbd5e1;
}

:global(html.dark) .form-control {
    background: #0f172a;
    border-color: #334155;
    color: #f9fafb;
}

:global(html.dark) .form-control::placeholder {
    color: #64748b;
}

:global(html.dark) .form-control:hover {
    border-color: #475569;
}

:global(html.dark) .form-control:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.20);
}

:global(html.dark) .input-icon {
    color: #64748b;
}

:global(html.dark) .form-control.readonly {
    background: #1e293b;
    color: #94a3b8;
}

:global(html.dark) .form-actions {
    border-top-color: #1f2937;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 640px) {
    .profile-page {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .profile-card {
        padding: 22px;
    }

    .avatar-section {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .avatar {
        width: 72px;
        height: 72px;
        font-size: 26px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn-primary {
        width: 100%;
    }
}

</style>