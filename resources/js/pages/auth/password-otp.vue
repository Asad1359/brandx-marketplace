<template>
    <div class="auth-page">
        <div class="auth-card">

            <div class="text-center mb-4">
                <h2>Verify Reset OTP</h2>

                <p>
                    Enter the OTP sent to
                    <strong>{{ email }}</strong>
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

            <form @submit.prevent="verifyOtp">

                <div class="mb-4">
                    <label class="form-label">
                        6-Digit OTP
                    </label>

                    <input
                        v-model="otp"
                        type="text"
                        inputmode="numeric"
                        maxlength="6"
                        class="form-control otp-input"
                        placeholder="000000"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary w-100"
                    :disabled="loading"
                >
                    {{ loading ? 'Verifying...' : 'Verify OTP' }}
                </button>

            </form>

            <div class="text-center mt-4">

                <button
                    type="button"
                    class="btn btn-link"
                    :disabled="resending"
                    @click="resendOtp"
                >
                    {{ resending ? 'Sending...' : 'Resend OTP' }}
                </button>

            </div>

            <div class="text-center mt-2">
                <router-link to="/login">
                    Back to Login
                </router-link>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import {
    verifyPasswordOtp,
    resendPasswordOtp
} from '../../services/auth';

const route = useRoute();
const router = useRouter();

const email = ref('');
const otp = ref('');

const loading = ref(false);
const resending = ref(false);

const error = ref('');
const success = ref('');

onMounted(() => {
    email.value =
        route.query.email ||
        sessionStorage.getItem(
            'password_reset_email'
        ) ||
        '';
});

async function verifyOtp() {
    error.value = '';
    success.value = '';

    if (!email.value) {
        error.value =
            'Email was not found.';
        return;
    }

    if (!/^\d{6}$/.test(otp.value)) {
        error.value =
            'Please enter a valid 6-digit OTP.';
        return;
    }

    loading.value = true;

    try {
        const response = await verifyPasswordOtp({
            email: email.value,
            otp: otp.value,
        });

        /*
         * Save reset token if backend returns one.
         */
        const token =
            response.data?.token ||
            response.data?.reset_token;

        if (token) {
            sessionStorage.setItem(
                'password_reset_token',
                token
            );
        }

        success.value =
            'OTP verified successfully.';

        setTimeout(() => {
            router.push({
                path: '/reset-password',
                query: {
                    email: email.value,
                },
            });
        }, 500);

    } catch (err) {
        console.error(
            'Password OTP error:',
            err
        );

        error.value =
            err.response?.data?.message ||
            'Invalid or expired OTP.';
    } finally {
        loading.value = false;
    }
}

async function resendOtp() {
    error.value = '';
    success.value = '';

    if (!email.value) {
        error.value =
            'Email was not found.';
        return;
    }

    resending.value = true;

    try {
        await resendPasswordOtp({
            email: email.value,
        });

        success.value =
            'A new OTP has been sent.';
    } catch (err) {
        console.error(
            'Resend password OTP error:',
            err
        );

        error.value =
            err.response?.data?.message ||
            'Unable to resend OTP.';
    } finally {
        resending.value = false;
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

.otp-input {
    text-align: center;
    font-size: 25px;
    letter-spacing: 8px;
    font-weight: 700;
}
</style>