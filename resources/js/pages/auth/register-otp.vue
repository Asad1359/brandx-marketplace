<template>
    <div class="otp-page">
        <div class="otp-card">

            <!-- Logo / Brand -->
            <div class="brand">
                <h1>BrandX</h1>
                <p>Email Verification</p>
            </div>

            <!-- Icon -->
            <div class="otp-icon">
                ✉
            </div>

            <h2>Verify Your Email</h2>

            <p class="description">
                We have sent a 6-digit verification code to:
            </p>

            <p class="email">
                {{ email || 'your email address' }}
            </p>

            <!-- OTP Form -->
            <form @submit.prevent="verifyOtp">

                <label for="otp">Verification Code</label>

                <input
                    id="otp"
                    v-model="otp"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    autocomplete="one-time-code"
                    placeholder="Enter 6-digit OTP"
                    :disabled="loading"
                    @input="cleanOtp"
                />

                <p v-if="errorMessage" class="error">
                    {{ errorMessage }}
                </p>

                <p v-if="successMessage" class="success">
                    {{ successMessage }}
                </p>

                <button
                    type="submit"
                    :disabled="loading || otp.length !== 6"
                >
                    {{ loading ? 'Verifying...' : 'Verify Email' }}
                </button>
            </form>

            <!-- Resend -->
            <div class="resend-section">

                <p v-if="countdown > 0">
                    Didn't receive the code?
                    <strong>Resend in {{ countdown }}s</strong>
                </p>

                <p v-else>
                    Didn't receive the code?
                    <button
                        type="button"
                        class="resend-button"
                        :disabled="resending"
                        @click="resendOtp"
                    >
                        {{ resending ? 'Sending...' : 'Resend OTP' }}
                    </button>
                </p>

            </div>

            <!-- Back -->
            <button
                type="button"
                class="back-button"
                @click="goBack"
            >
                ← Back to Register
            </button>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

import {
    verifyRegistrationOtp,
    resendRegistrationOtp
} from '../../services/auth';

const router = useRouter();
const route = useRoute();

const email = ref('');
const otp = ref('');

const loading = ref(false);
const resending = ref(false);

const errorMessage = ref('');
const successMessage = ref('');

const countdown = ref(0);

let countdownTimer = null;


/*
|--------------------------------------------------------------------------
| Get Email
|--------------------------------------------------------------------------
*/

onMounted(() => {
    /*
     * Email can come from:
     * /register-otp?email=test@example.com
     *
     * or localStorage from registration.
     */

    const routeEmail = route.query.email;

    const storedEmail = localStorage.getItem('registration_email');

    email.value = routeEmail || storedEmail || '';

    if (routeEmail) {
        localStorage.setItem('registration_email', routeEmail);
    }

    startCountdown();
});


/*
|--------------------------------------------------------------------------
| Clean OTP Input
|--------------------------------------------------------------------------
*/

function cleanOtp() {
    otp.value = otp.value
        .replace(/\D/g, '')
        .slice(0, 6);
}


/*
|--------------------------------------------------------------------------
| Verify OTP
|--------------------------------------------------------------------------
*/

async function verifyOtp() {
    errorMessage.value = '';
    successMessage.value = '';

    if (!email.value) {
        errorMessage.value = 'Registration email is missing.';
        return;
    }

    if (otp.value.length !== 6) {
        errorMessage.value = 'Please enter the complete 6-digit OTP.';
        return;
    }

    loading.value = true;

    try {
        const response = await verifyRegistrationOtp(
            email.value,
            otp.value
        );

        if (response.success) {

            successMessage.value =
                response.message || 'Email verified successfully.';

            /*
             * Save token if backend returned one.
             */
            if (response.token) {
                localStorage.setItem(
                    'auth_token',
                    response.token
                );
            }

            /*
             * Save user if returned.
             */
            if (response.user) {
                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(response.user)
                );
            }

            /*
             * Remove registration email.
             */
            localStorage.removeItem('registration_email');

            /*
             * Redirect according to role.
             */
            setTimeout(() => {

                if (response.user?.role === 'admin') {
                    router.push('/admindashboard');
                } else {
                    router.push('/');
                }

            }, 700);

        } else {
            errorMessage.value =
                response.message || 'Invalid OTP.';
        }

    } catch (error) {

        console.error('OTP verification error:', error);

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to verify OTP. Please try again.';

    } finally {
        loading.value = false;
    }
}


/*
|--------------------------------------------------------------------------
| Resend OTP
|--------------------------------------------------------------------------
*/

async function resendOtp() {
    errorMessage.value = '';
    successMessage.value = '';

    if (!email.value) {
        errorMessage.value = 'Registration email is missing.';
        return;
    }

    if (countdown.value > 0) {
        return;
    }

    resending.value = true;

    try {
        const response = await resendRegistrationOtp(
            email.value
        );

        if (response.success) {

            successMessage.value =
                response.message || 'A new OTP has been sent.';

            otp.value = '';

            startCountdown();

        } else {

            errorMessage.value =
                response.message || 'Unable to resend OTP.';

        }

    } catch (error) {

        console.error('Resend OTP error:', error);

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to resend OTP. Please try again.';

    } finally {
        resending.value = false;
    }
}


/*
|--------------------------------------------------------------------------
| Countdown
|--------------------------------------------------------------------------
*/

function startCountdown() {
    countdown.value = 60;

    if (countdownTimer) {
        clearInterval(countdownTimer);
    }

    countdownTimer = setInterval(() => {

        if (countdown.value > 0) {
            countdown.value--;
        } else {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }

    }, 1000);
}


/*
|--------------------------------------------------------------------------
| Go Back
|--------------------------------------------------------------------------
*/

function goBack() {
    router.push('/register');
}


/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onUnmounted(() => {

    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }

});
</script>

<style scoped>
* {
    box-sizing: border-box;
}

.otp-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 16px;
    background: linear-gradient(
        135deg,
        #eef2ff 0%,
        #f8fafc 50%,
        #e0f2fe 100%
    );
}

.otp-card {
    width: 100%;
    max-width: 460px;
    padding: 40px 35px;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.12);
    text-align: center;
}

.brand h1 {
    margin: 0;
    font-size: 32px;
    font-weight: 800;
    color: #2563eb;
}

.brand p {
    margin: 6px 0 25px;
    color: #64748b;
    font-size: 14px;
}

.otp-icon {
    width: 70px;
    height: 70px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eff6ff;
    color: #2563eb;
    font-size: 32px;
}

.otp-card h2 {
    margin: 0 0 12px;
    color: #1e293b;
    font-size: 25px;
}

.description {
    margin: 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.email {
    margin: 8px 0 28px;
    color: #2563eb;
    font-weight: 700;
    word-break: break-word;
}

form {
    text-align: left;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-size: 14px;
    font-weight: 600;
}

input {
    width: 100%;
    height: 54px;
    padding: 0 15px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    outline: none;
    text-align: center;
    letter-spacing: 8px;
    font-size: 22px;
    font-weight: 700;
    transition: 0.2s;
}

input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

input::placeholder {
    letter-spacing: normal;
    font-size: 14px;
    font-weight: 400;
}

form > button {
    width: 100%;
    height: 52px;
    margin-top: 18px;
    border: none;
    border-radius: 10px;
    background: #2563eb;
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
}

form > button:hover:not(:disabled) {
    background: #1d4ed8;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error {
    margin: 10px 0 0;
    padding: 10px;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    font-size: 13px;
}

.success {
    margin: 10px 0 0;
    padding: 10px;
    border-radius: 8px;
    background: #f0fdf4;
    color: #16a34a;
    font-size: 13px;
}

.resend-section {
    margin-top: 25px;
    color: #64748b;
    font-size: 14px;
}

.resend-button {
    border: none;
    padding: 0;
    background: transparent;
    color: #2563eb;
    font-weight: 700;
    cursor: pointer;
}

.resend-button:hover:not(:disabled) {
    text-decoration: underline;
}

.back-button {
    margin-top: 22px;
    border: none;
    background: transparent;
    color: #475569;
    font-size: 14px;
    cursor: pointer;
}

.back-button:hover {
    color: #2563eb;
}

@media (max-width: 500px) {
    .otp-card {
        padding: 30px 22px;
    }

    .otp-card h2 {
        font-size: 22px;
    }
}
</style>