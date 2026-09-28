<template>
    <div class="auth-page">

        <div class="auth-card">

            <!-- Header -->
            <div class="auth-header">
                <div class="auth-icon">
                    👤
                </div>

                <h2>Create Account</h2>

                <p>
                    Register a new account
                </p>
            </div>

            <!-- Error -->
            <div
                v-if="error"
                class="alert alert-danger"
            >
                <span class="error-icon">!</span>
                <span>{{ error }}</span>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleRegister">

                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">
                        Name
                    </label>

                    <input
                        v-model="form.name"
                        type="text"
                        class="form-control"
                        placeholder="Enter your name"
                        autocomplete="name"
                        required
                    >
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">
                        Email
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        class="form-control"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            v-model="form.password"
                            :type="
                                showPassword
                                    ? 'text'
                                    : 'password'
                            "
                            class="form-control password-input"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            @click="
                                showPassword =
                                    !showPassword
                            "
                            :aria-label="
                                showPassword
                                    ? 'Hide password'
                                    : 'Show password'
                            "
                        >
                            {{
                                showPassword
                                    ? '🙈'
                                    : '👁️'
                            }}
                        </button>

                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label">
                        Confirm Password
                    </label>

                    <div class="password-wrapper">

                        <input
                            v-model="
                                form.password_confirmation
                            "
                            :type="
                                showConfirmPassword
                                    ? 'text'
                                    : 'password'
                            "
                            class="form-control password-input"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            @click="
                                showConfirmPassword =
                                    !showConfirmPassword
                            "
                            :aria-label="
                                showConfirmPassword
                                    ? 'Hide confirm password'
                                    : 'Show confirm password'
                            "
                        >
                            {{
                                showConfirmPassword
                                    ? '🙈'
                                    : '👁️'
                            }}
                        </button>

                    </div>
                </div>

                <!-- Button -->
                <button
                    type="submit"
                    class="register-btn"
                    :disabled="loading"
                >

                    <span
                        v-if="loading"
                        class="spinner"
                    ></span>

                    {{
                        loading
                            ? 'Registering...'
                            : 'Register'
                    }}

                </button>

            </form>

            <!-- Login -->
            <div class="login-section">

                <span>
                    Already have an account?
                </span>

                <router-link
                    to="/login"
                    class="login-link"
                >
                    Login
                </router-link>

            </div>

        </div>

    </div>
</template>


<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

import { register } from '../../services/auth';

const router = useRouter();

const loading = ref(false);
const error = ref('');

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});


async function handleRegister() {
    error.value = '';

    if (
        form.password !==
        form.password_confirmation
    ) {
        error.value =
            'Passwords do not match.';

        return;
    }

    loading.value = true;

    try {
        await register(form);

        sessionStorage.setItem(
            'register_email',
            form.email
        );

        router.push({
            path: '/register-otp',
            query: {
                email: form.email,
            },
        });

    } catch (err) {
        console.error(
            'Register error:',
            err
        );

        const errors =
            err.response?.data?.errors;

        if (errors) {
            const firstError =
                Object.values(errors)[0];

            error.value =
                Array.isArray(firstError)
                    ? firstError[0]
                    : String(firstError);

        } else {
            error.value =
                err.response?.data?.message ||
                'Unable to register.';
        }

    } finally {
        loading.value = false;
    }
}
</script>


<style scoped>

/* =========================================
   PAGE
========================================= */

.auth-page {
    min-height: 100vh;

    display: flex;
    justify-content: center;
    align-items: center;

    padding: 30px 20px;

    background:
        linear-gradient(
            135deg,
            #f5f7fb 0%,
            #eef3f9 100%
        );
}


/* =========================================
   REGISTER CARD
========================================= */

.auth-card {
    width: 100%;

    /*
     * Proper register form width
     */
    max-width: 440px;

    padding: 36px 38px;

    background: #ffffff;

    border-radius: 16px;

    border: 1px solid #e8ecf2;

    box-shadow:
        0 12px 35px
        rgba(0, 0, 0, 0.08);
}


/* =========================================
   HEADER
========================================= */

.auth-header {
    text-align: center;

    margin-bottom: 28px;
}


.auth-icon {
    width: 55px;
    height: 55px;

    margin: 0 auto 15px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #4f8dfd
        );

    color: #ffffff;

    font-size: 23px;

    box-shadow:
        0 7px 18px
        rgba(13, 110, 253, 0.20);
}


.auth-card h2 {
    margin: 0;

    color: #202938;

    font-size: 27px;
    font-weight: 700;
}


.auth-card p {
    margin: 7px 0 0;

    color: #7b8492;

    font-size: 14px;
}


/* =========================================
   ERROR
========================================= */

.alert-danger {
    display: flex;
    align-items: center;

    gap: 9px;

    width: 100%;

    margin-bottom: 22px;

    padding: 11px 13px;

    border: 1px solid #f3c2c7;

    border-radius: 8px;

    background: #fff5f6;

    color: #b4232d;

    font-size: 13px;
}


.error-icon {
    width: 19px;
    height: 19px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #dc3545;

    color: #ffffff;

    font-size: 12px;
    font-weight: 700;
}


/* =========================================
   FORM GROUP
========================================= */

.form-group {
    width: 100%;

    margin-bottom: 19px;
}


/* =========================================
   LABEL
========================================= */

.form-label {
    display: block;

    width: 100%;

    margin-bottom: 7px;

    color: #374151;

    font-size: 13px;

    font-weight: 600;
}


/* =========================================
   INPUT
========================================= */

.form-control {
    display: block;

    width: 100%;

    height: 48px;

    padding: 0 14px;

    box-sizing: border-box;

    border: 1px solid #d9dee7;

    border-radius: 8px;

    background: #ffffff;

    color: #1f2937;

    font-size: 14px;

    outline: none;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}


.form-control::placeholder {
    color: #a1a8b3;
}


.form-control:hover {
    border-color: #b9c1cc;
}


.form-control:focus {
    border-color: #0d6efd;

    box-shadow:
        0 0 0 3px
        rgba(13, 110, 253, 0.10);
}


/* =========================================
   PASSWORD
========================================= */

.password-wrapper {
    position: relative;

    width: 100%;
}


.password-input {
    padding-right: 48px;
}


.password-toggle {
    position: absolute;

    top: 50%;
    right: 7px;

    width: 35px;
    height: 35px;

    transform: translateY(-50%);

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border: none;

    border-radius: 7px;

    background: transparent;

    cursor: pointer;

    font-size: 17px;

    transition:
        background 0.2s ease;
}


.password-toggle:hover {
    background: #f1f4f8;
}


.password-toggle:focus {
    outline: none;
}


/* =========================================
   REGISTER BUTTON
========================================= */

.register-btn {
    width: 100%;

    height: 48px;

    margin-top: 4px;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    border: none;

    border-radius: 8px;

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #0b5ed7
        );

    color: #ffffff;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

    box-shadow:
        0 6px 14px
        rgba(13, 110, 253, 0.18);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}


.register-btn:hover:not(:disabled) {
    background:
        linear-gradient(
            135deg,
            #0b5ed7,
            #084298
        );

    transform: translateY(-1px);

    box-shadow:
        0 8px 18px
        rgba(13, 110, 253, 0.25);
}


.register-btn:active:not(:disabled) {
    transform: translateY(0);
}


.register-btn:disabled {
    opacity: 0.7;

    cursor: not-allowed;

    box-shadow: none;
}


/* =========================================
   SPINNER
========================================= */

.spinner {
    width: 15px;
    height: 15px;

    border: 2px solid
        rgba(255, 255, 255, 0.4);

    border-top-color: #ffffff;

    border-radius: 50%;

    animation: spin 0.7s linear infinite;
}


/* =========================================
   LOGIN
========================================= */

.login-section {
    margin-top: 24px;

    text-align: center;

    color: #737b88;

    font-size: 13px;
}


.login-link {
    margin-left: 5px;

    color: #0d6efd;

    font-weight: 600;

    text-decoration: none;
}


.login-link:hover {
    color: #084298;

    text-decoration: underline;
}


/* =========================================
   ANIMATION
========================================= */

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 576px) {

    .auth-page {
        padding: 20px 15px;
    }

    .auth-card {
        max-width: 100%;

        padding: 30px 22px;

        border-radius: 14px;
    }

    .auth-card h2 {
        font-size: 24px;
    }

    .form-control {
        height: 47px;
    }

    .register-btn {
        height: 47px;
    }
}

</style>
