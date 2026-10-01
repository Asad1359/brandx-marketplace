<template>

    <div class="login-page">

        <div class="login-card">

            <div class="login-header">

                <h1>
                    Welcome Back
                </h1>

                <p>
                    Login to your BrandX account
                </p>

            </div>


            <!-- ERROR -->

            <div
                v-if="errorMessage"
                class="alert error"
            >
                {{ errorMessage }}
            </div>


            <!-- SUCCESS -->

            <div
                v-if="successMessage"
                class="alert success"
            >
                {{ successMessage }}
            </div>


            <!-- LOGIN FORM -->

            <form @submit.prevent="handleLogin">

                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        v-model="email"
                        type="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password
                    </label>

                    <input
                        v-model="password"
                        type="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <!-- FORGOT PASSWORD -->

                <div class="forgot">

                    <router-link
                        to="/forgot-password"
                    >
                        Forgot Password?
                    </router-link>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    :disabled="loading"
                >

                    <span v-if="loading">
                        Logging in...
                    </span>

                    <span v-else>
                        Login
                    </span>

                </button>

            </form>


            <!-- REGISTER -->

            <div class="register">

                <span>
                    Don't have an account?
                </span>

                <router-link
                    to="/register"
                >
                    Register
                </router-link>

            </div>

        </div>

    </div>

</template>


<script setup>

import {
    ref
} from 'vue';

import {
    login
} from '../../services/auth';

import {
    setAuthUser
} from '../../stores/auth';


/*
|--------------------------------------------------------------------------
| FORM DATA
|--------------------------------------------------------------------------
*/

const email = ref('');

const password = ref('');


/*
|--------------------------------------------------------------------------
| STATES
|--------------------------------------------------------------------------
*/

const loading = ref(false);

const errorMessage = ref('');

const successMessage = ref('');


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

const handleLogin = async () => {

    errorMessage.value = '';

    successMessage.value = '';

    loading.value = true;


    try {

        /*
        |------------------------------------------------------------------
        | API REQUEST
        |------------------------------------------------------------------
        */

        const response = await login(
            email.value.trim(),
            password.value
        );


        /*
        |------------------------------------------------------------------
        | DEBUG
        |------------------------------------------------------------------
        */

        console.log('LOGIN RESPONSE:', response.data);


        /*
        |------------------------------------------------------------------
        | FAILED
        |------------------------------------------------------------------
        */

        if (!response.data.success) {

            errorMessage.value =
                response.data.message ||
                'Login failed.';

            loading.value = false;

            return;
        }


        /*
        |------------------------------------------------------------------
        | OTP REQUIRED (email not verified)
        |------------------------------------------------------------------
        */

        if (response.data.requires_otp) {

            localStorage.setItem(
                'registration_email',
                response.data.email
            );

            /*
            |--------------------------------------------------------------
            | Redirect to OTP page
            |--------------------------------------------------------------
            */

            window.location.href = '/register-otp';

            return;
        }


        /*
        |------------------------------------------------------------------
        | GET USER
        |------------------------------------------------------------------
        */

        const user = response.data.user;


        if (!user) {

            errorMessage.value =
                'Login successful but user information was not returned.';

            loading.value = false;

            return;
        }


        /*
        |------------------------------------------------------------------
        | SAVE USER (localStorage + authState)
        |------------------------------------------------------------------
        */

        setAuthUser(user);


        /*
        |------------------------------------------------------------------
        | ROLE-BASED REDIRECT
        |------------------------------------------------------------------
        | IMPORTANT: Using `window.location.href` (full page reload) instead
        | of `router.replace()`. This ensures:
        |   1. Session cookies are properly applied by the browser
        |   2. Fresh page load — no stale auth state
        |   3. Router guard runs cleanly on next page load
        |------------------------------------------------------------------
        */

        if (user.role === 'admin') {

            console.log('USER ROLE: ADMIN — redirecting to /admin/dashboard');

            window.location.href = '/admin/dashboard';

        } else {

            console.log('USER ROLE: USER — redirecting to /dashboard');

            window.location.href = '/dashboard';
        }


    } catch (error) {

        console.error('LOGIN ERROR:', error);


        if (error.response) {

            console.error('STATUS:', error.response.status);

            console.error('DATA:', error.response.data);
        }


        errorMessage.value =
            error.response?.data?.message ||
            'Login failed. Please check your email and password.';

        loading.value = false;
    }
};

</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.login-page {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: #f5f7fb;
}


/*
|--------------------------------------------------------------------------
| CARD
|--------------------------------------------------------------------------
*/

.login-card {

    width: 100%;

    max-width: 430px;

    padding: 35px;

    background: white;

    border-radius: 16px;

    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.08);
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.login-header {

    text-align: center;

    margin-bottom: 30px;
}


.login-header h1 {

    margin: 0 0 8px;

    font-size: 30px;
}


.login-header p {

    margin: 0;

    color: #6b7280;
}


/*
|--------------------------------------------------------------------------
| FORM GROUP
|--------------------------------------------------------------------------
*/

.form-group {

    margin-bottom: 20px;
}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-weight: 600;
}


.form-group input {

    width: 100%;

    box-sizing: border-box;

    padding: 13px 14px;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    outline: none;

    font-size: 15px;
}


.form-group input:focus {

    border-color: #4f46e5;
}


/*
|--------------------------------------------------------------------------
| FORGOT
|--------------------------------------------------------------------------
*/

.forgot {

    text-align: right;

    margin-bottom: 20px;
}


.forgot a {

    color: #4f46e5;

    text-decoration: none;

    font-size: 14px;
}


/*
|--------------------------------------------------------------------------
| SUBMIT BUTTON
|--------------------------------------------------------------------------
*/

button[type="submit"] {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #4f46e5;

    color: white;

    font-size: 16px;

    font-weight: 600;

    cursor: pointer;

    transition: background 0.2s ease;
}


button[type="submit"]:hover:not(:disabled) {

    background: #4338ca;
}


button[type="submit"]:disabled {

    opacity: 0.6;

    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| ALERTS
|--------------------------------------------------------------------------
*/

.alert {

    padding: 12px 14px;

    margin-bottom: 20px;

    border-radius: 8px;

    font-size: 14px;
}


.alert.error {

    background: #fee2e2;

    color: #991b1b;
}


.alert.success {

    background: #dcfce7;

    color: #166534;
}


/*
|--------------------------------------------------------------------------
| REGISTER
|--------------------------------------------------------------------------
*/

.register {

    text-align: center;

    margin-top: 25px;

    color: #6b7280;
}


.register a {

    margin-left: 6px;

    color: #4f46e5;

    text-decoration: none;

    font-weight: 600;
}

</style>