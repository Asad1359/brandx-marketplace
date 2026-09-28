<template>
    <div class="auth-page">
        <div class="auth-card">

            <div class="text-center mb-4">
                <h2>Forgot Password?</h2>

                <p>
                    Enter your email to receive a password
                    reset OTP.
                </p>
            </div>

            <div
                v-if="error"
                class="alert alert-danger"
            >
                {{ error }}
            </div>

            <form @submit.prevent="handleForgotPassword">

                <div class="mb-3">
                    <label class="form-label">
                        Email
                    </label>

                    <input
                        v-model="email"
                        type="email"
                        class="form-control"
                        placeholder="Enter your email"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                    :disabled="loading"
                >
                    {{ loading ? 'Sending...' : 'Send OTP' }}
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';

import { forgotPassword } from '../../services/auth';

const router = useRouter();

const email = ref('');
const loading = ref(false);
const error = ref('');

async function handleForgotPassword() {
    loading.value = true;
    error.value = '';

    try {
        await forgotPassword({
            email: email.value,
        });

        sessionStorage.setItem(
            'password_reset_email',
            email.value
        );

        router.push({
            path: '/password-otp',
            query: {
                email: email.value,
            },
        });

    } catch (err) {
        console.error(
            'Forgot password error:',
            err
        );

        error.value =
            err.response?.data?.message ||
            'Unable to send password reset OTP.';
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