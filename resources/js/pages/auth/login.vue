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
    useRouter
} from 'vue-router';

import {
    login,
    saveUser
} from '../../services/auth';


/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/

const router = useRouter();


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
        |--------------------------------------------------------------------------
        | API REQUEST
        |--------------------------------------------------------------------------
        */

        const response = await login(
            email.value.trim(),
            password.value
        );


        /*
        |--------------------------------------------------------------------------
        | DEBUG
        |--------------------------------------------------------------------------
        */

        console.log(
            'LOGIN RESPONSE:',
            response.data
        );


        /*
        |--------------------------------------------------------------------------
        | FAILED
        |--------------------------------------------------------------------------
        */

        if (!response.data.success) {

            errorMessage.value =
                response.data.message ||
                'Login failed.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | OTP REQUIRED
        |--------------------------------------------------------------------------
        */

        if (response.data.requires_otp) {

            localStorage.setItem(
                'registration_email',
                response.data.email
            );


            router.push({
                name: 'register.otp'
            });


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        const user = response.data.user;


        if (!user) {

            errorMessage.value =
                'Login successful but user information was not returned.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | SAVE USER
        |--------------------------------------------------------------------------
        */

        saveUser(user);


        /*
        |--------------------------------------------------------------------------
        | ROLE CHECK
        |--------------------------------------------------------------------------
        */

        if (user.role === 'admin') {

            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            console.log(
                'USER ROLE: ADMIN'
            );

            await router.replace({
                name: 'admin.dashboard'
            });

        } else {

            /*
            |--------------------------------------------------------------------------
            | NORMAL USER
            |--------------------------------------------------------------------------
            */

            console.log(
                'USER ROLE: USER'
            );

            await router.replace({
                name: 'user.dashboard'
            });
        }


    } catch (error) {

        console.error(
            'LOGIN ERROR:',
            error
        );


        if (error.response) {

            console.error(
                'STATUS:',
                error.response.status
            );

            console.error(
                'DATA:',
                error.response.data
            );
        }


        errorMessage.value =
            error.response?.data?.message ||
            'Login failed. Please check your email and password.';


    } finally {

        loading.value = false;
    }

};

</script>


<style scoped>

.login-page {

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background: #f5f7fb;
}


.login-card {

    width: 100%;

    max-width: 430px;

    padding: 35px;

    background: white;

    border-radius: 16px;

    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.08);
}


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


.forgot {

    text-align: right;

    margin-bottom: 20px;
}


.forgot a {

    color: #4f46e5;

    text-decoration: none;

    font-size: 14px;
}


button {

    width: 100%;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #4f46e5;

    color: white;

    font-size: 16px;

    font-weight: 600;

    cursor: pointer;
}


button:disabled {

    opacity: 0.6;

    cursor: not-allowed;
}


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