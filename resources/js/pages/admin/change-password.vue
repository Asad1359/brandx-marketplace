<template>
    <div class="admin-page">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h1>Change Password</h1>
            <p>Update your admin account password.</p>
        </div>


        <!-- SUCCESS MESSAGE -->
        <div
            v-if="success"
            class="alert alert-success"
        >
            {{ success }}
        </div>


        <!-- ERROR MESSAGE -->
        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>


        <!-- PASSWORD CARD -->
        <div class="card password-card">
            <div class="card-body">

                <form @submit.prevent="changePassword">

                    <!-- CURRENT PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">
                            Current Password
                        </label>

                        <div class="password-wrapper">

                            <input
                                v-model="form.current_password"
                                :type="
                                    showCurrent
                                        ? 'text'
                                        : 'password'
                                "
                                class="form-control"
                                autocomplete="current-password"
                                placeholder="Enter current password"
                                required
                            />

                            <button
                                type="button"
                                class="eye-button"
                                @click="
                                    showCurrent = !showCurrent
                                "
                                :aria-label="
                                    showCurrent
                                        ? 'Hide current password'
                                        : 'Show current password'
                                "
                            >
                                <!-- OPEN EYE -->
                                <svg
                                    v-if="!showCurrent"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- CLOSED EYE -->
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3 3l18 18" />
                                    <path
                                        d="M10.6 10.6a2 2 0 0 0 2.8 2.8"
                                    />
                                    <path
                                        d="M9.9 4.2A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-3.2 4.4"
                                    />
                                    <path
                                        d="M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8c1.5 0 2.8-.3 4-.8"
                                    />
                                </svg>
                            </button>

                        </div>
                    </div>


                    <!-- NEW PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">
                            New Password
                        </label>

                        <div class="password-wrapper">

                            <input
                                v-model="form.password"
                                :type="
                                    showPassword
                                        ? 'text'
                                        : 'password'
                                "
                                class="form-control"
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Enter new password"
                                required
                            />

                            <button
                                type="button"
                                class="eye-button"
                                @click="
                                    showPassword = !showPassword
                                "
                                :aria-label="
                                    showPassword
                                        ? 'Hide new password'
                                        : 'Show new password'
                                "
                            >
                                <!-- OPEN EYE -->
                                <svg
                                    v-if="!showPassword"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- CLOSED EYE -->
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3 3l18 18" />
                                    <path
                                        d="M10.6 10.6a2 2 0 0 0 2.8 2.8"
                                    />
                                    <path
                                        d="M9.9 4.2A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-3.2 4.4"
                                    />
                                    <path
                                        d="M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8c1.5 0 2.8-.3 4-.8"
                                    />
                                </svg>
                            </button>

                        </div>
                    </div>


                    <!-- CONFIRM PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">
                            Confirm New Password
                        </label>

                        <div class="password-wrapper">

                            <input
                                v-model="form.password_confirmation"
                                :type="
                                    showConfirmation
                                        ? 'text'
                                        : 'password'
                                "
                                class="form-control"
                                autocomplete="new-password"
                                minlength="8"
                                placeholder="Confirm new password"
                                required
                            />

                            <button
                                type="button"
                                class="eye-button"
                                @click="
                                    showConfirmation =
                                        !showConfirmation
                                "
                                :aria-label="
                                    showConfirmation
                                        ? 'Hide confirmation password'
                                        : 'Show confirmation password'
                                "
                            >
                                <!-- OPEN EYE -->
                                <svg
                                    v-if="!showConfirmation"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                    />
                                </svg>

                                <!-- CLOSED EYE -->
                                <svg
                                    v-else
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M3 3l18 18" />
                                    <path
                                        d="M10.6 10.6a2 2 0 0 0 2.8 2.8"
                                    />
                                    <path
                                        d="M9.9 4.2A10.7 10.7 0 0 1 12 4c6.5 0 10 8 10 8a18.5 18.5 0 0 1-3.2 4.4"
                                    />
                                    <path
                                        d="M6.6 6.6C3.7 8.5 2 12 2 12s3.5 8 10 8c1.5 0 2.8-.3 4-.8"
                                    />
                                </svg>
                            </button>

                        </div>
                    </div>


                    <!-- BUTTON -->
                    <button
                        type="submit"
                        class="btn btn-primary change-btn"
                        :disabled="loading"
                    >
                        <span v-if="loading">
                            Updating...
                        </span>

                        <span v-else>
                            Change Password
                        </span>
                    </button>

                </form>

            </div>
        </div>

    </div>
</template>


<script setup>
import { reactive, ref } from 'vue';
import { changeAdminPassword } from '../../services/admin';


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const loading = ref(false);
const error = ref('');
const success = ref('');


/*
|--------------------------------------------------------------------------
| PASSWORD VISIBILITY
|--------------------------------------------------------------------------
*/

const showCurrent = ref(false);
const showPassword = ref(false);
const showConfirmation = ref(false);


/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});


/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

async function changePassword() {

    loading.value = true;

    error.value = '';
    success.value = '';


    /*
    |----------------------------------------------------------------------
    | VALIDATION
    |----------------------------------------------------------------------
    */

    if (!form.current_password) {
        error.value = 'Please enter your current password.';
        loading.value = false;
        return;
    }


    if (!form.password) {
        error.value = 'Please enter a new password.';
        loading.value = false;
        return;
    }


    if (form.password.length < 8) {
        error.value =
            'New password must be at least 8 characters.';
        loading.value = false;
        return;
    }


    if (!form.password_confirmation) {
        error.value =
            'Please confirm your new password.';
        loading.value = false;
        return;
    }


    if (
        form.password !==
        form.password_confirmation
    ) {
        error.value =
            'New password and confirmation password do not match.';
        loading.value = false;
        return;
    }


    /*
    |----------------------------------------------------------------------
    | API REQUEST
    |----------------------------------------------------------------------
    */

    try {

        const response =
            await changeAdminPassword(form);


        /*
        |------------------------------------------------------------------
        | SUCCESS
        |------------------------------------------------------------------
        */

        success.value =
            response.data?.message ||
            'Password changed successfully.';


        /*
        |------------------------------------------------------------------
        | CLEAR FORM
        |------------------------------------------------------------------
        */

        form.current_password = '';
        form.password = '';
        form.password_confirmation = '';

        showCurrent.value = false;
        showPassword.value = false;
        showConfirmation.value = false;

    } catch (err) {

        console.error(
            'Change password error:',
            err
        );


        /*
        |------------------------------------------------------------------
        | VALIDATION ERRORS
        |------------------------------------------------------------------
        */

        if (err.response?.data?.errors) {

            const errors =
                err.response.data.errors;

            const firstError =
                Object.values(errors)[0];

            error.value =
                Array.isArray(firstError)
                    ? firstError[0]
                    : firstError;

        } else {

            error.value =
                err.response?.data?.message ||
                'Unable to change password.';
        }

    } finally {

        loading.value = false;

    }
}
</script>


<style scoped>

.admin-page {
    padding: 24px;
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.page-header {
    margin-bottom: 25px;
}

.page-header h1 {
    margin-bottom: 6px;

    font-size: 28px;
    font-weight: 700;

    color: #212529;
}

.page-header p {
    margin: 0;

    color: #6c757d;
}


/*
|--------------------------------------------------------------------------
| ALERT
|--------------------------------------------------------------------------
*/

.alert {
    max-width: 800px;
    margin-bottom: 20px;
}


/*
|--------------------------------------------------------------------------
| PASSWORD CARD
|--------------------------------------------------------------------------
*/

.password-card {
    width: 100%;
    max-width: 800px;

    border: none;
    border-radius: 12px;

    box-shadow:
        0 2px 12px rgba(0, 0, 0, 0.08);
}

.password-card .card-body {
    padding: 30px;
}


/*
|--------------------------------------------------------------------------
| LABEL
|--------------------------------------------------------------------------
*/

.form-label {
    font-weight: 600;

    color: #343a40;

    margin-bottom: 8px;
}


/*
|--------------------------------------------------------------------------
| PASSWORD WRAPPER
|--------------------------------------------------------------------------
*/

.password-wrapper {
    position: relative;

    width: 100%;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

.password-wrapper .form-control {
    width: 100%;

    min-height: 48px;

    padding-right: 52px;

    box-sizing: border-box;
}

.password-wrapper .form-control:focus {
    box-shadow:
        0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}


/*
|--------------------------------------------------------------------------
| EYE BUTTON
|--------------------------------------------------------------------------
*/

.eye-button {
    position: absolute;

    top: 0;
    right: 0;

    width: 50px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border: none;
    outline: none;

    background: transparent;

    color: #6c757d;

    cursor: pointer;

    z-index: 10;

    transition:
        color 0.2s ease,
        background-color 0.2s ease;
}

.eye-button:hover {
    color: #0d6efd;
}

.eye-button:focus {
    color: #0d6efd;

    outline: none;
}

.eye-button svg {
    display: block;

    width: 22px;
    height: 22px;

    min-width: 22px;
    min-height: 22px;

    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| BUTTON
|--------------------------------------------------------------------------
*/

.change-btn {
    min-width: 160px;

    min-height: 44px;

    font-weight: 600;
}

.change-btn:disabled {
    cursor: not-allowed;

    opacity: 0.65;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .admin-page {
        padding: 18px;
    }

    .password-card .card-body {
        padding: 22px;
    }

    .page-header h1 {
        font-size: 24px;
    }

}

</style>