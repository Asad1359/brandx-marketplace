<template>
    <div class="auth-page">
        <div class="auth-card">

            <div class="text-center mb-4">
                <h2>Reset Password</h2>

                <p>
                    Create a new password for your account.
                </p>
            </div>

            <div
                v-if="error"
                class="alert alert-danger"
            >
                {{ error }}
            </div>

            <div
                v-if="success"
                class="alert alert-success"
            >
                {{ success }}
            </div>

            <form @submit.prevent="handleReset">

                <div class="mb-3">
                    <label class="form-label">
                        New Password
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        class="form-control"
                        placeholder="Enter new password"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        Confirm New Password
                    </label>

                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-control"
                        placeholder="Confirm new password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                    :disabled="loading"
                >
                    {{ loading ? 'Resetting...' : 'Reset Password' }}
                </button>

            </form>

            <div class="text-center mt-4">
                <router-link to="/login">
                    Back to Login
                </router-link>
            </div>

        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { resetPassword } from '../../services/auth';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const error = ref('');
const success = ref('');

const email = ref('');

const form = reactive({
    password: '',
    password_confirmation: '',
});

onMounted(() => {
    email.value =
        route.query.email ||
        sessionStorage.getItem(
            'password_reset_email'
        ) ||
        '';
});

async function handleReset() {
    error.value = '';
    success.value = '';

    if (!email.value) {
        error.value =
            'Email was not found.';
        return;
    }

    if (form.password !== form.password_confirmation) {
        error.value =
            'Passwords do not match.';
        return;
    }

    if (form.password.length < 8) {
        error.value =
            'Password must be at least 8 characters.';
        return;
    }

    loading.value = true;

    try {
        const token =
            sessionStorage.getItem(
                'password_reset_token'
            );

        const data = {
            email: email.value,
            password: form.password,
            password_confirmation:
                form.password_confirmation,
        };

        if (token) {
            data.token = token;
        }

        await resetPassword(data);

        sessionStorage.removeItem(
            'password_reset_email'
        );

        sessionStorage.removeItem(
            'password_reset_token'
        );

        success.value =
            'Password reset successfully. Redirecting to login...';

        form.password = '';
        form.password_confirmation = '';

        setTimeout(() => {
            router.push('/login');
        }, 1200);

    } catch (err) {
        console.error(
            'Reset password error:',
            err
        );

        error.value =
            err.response?.data?.message ||
            'Unable to reset password.';
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background: #f8f9fa;
}

.auth-card {
    width: 100%;
    max-width: 450px;
    background: #fff;
    padding: 35px;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
}

.auth-card h2 {
    font-weight: 700;
}

.auth-card p {
    color: #6c757d;
}
</style>