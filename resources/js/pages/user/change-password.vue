<template>
    <div class="page">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <div>
                <h2>Change Password</h2>
                <p>Update your account password.</p>
            </div>
        </div>

        <!-- PASSWORD CARD -->
        <div class="password-card">

            <form @submit.prevent="changePassword">

                <!-- CURRENT PASSWORD -->
                <div class="form-group">
                    <label for="current_password">
                        Current Password
                    </label>

                    <div class="password-input-wrapper">

                        <input
                            id="current_password"
                            v-model="form.current_password"
                            :type="showCurrent ? 'text' : 'password'"
                            placeholder="Enter current password"
                            autocomplete="current-password"
                        />

                        <button
                            type="button"
                            class="eye-button"
                            @click="showCurrent = !showCurrent"
                            :aria-label="
                                showCurrent
                                    ? 'Hide current password'
                                    : 'Show current password'
                            "
                        >
                            <!-- SHOW EYE -->
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
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            <!-- HIDE EYE -->
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
                <div class="form-group">
                    <label for="password">
                        New Password
                    </label>

                    <div class="password-input-wrapper">

                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Enter new password"
                            autocomplete="new-password"
                        />

                        <button
                            type="button"
                            class="eye-button"
                            @click="showPassword = !showPassword"
                            :aria-label="
                                showPassword
                                    ? 'Hide new password'
                                    : 'Show new password'
                            "
                        >
                            <!-- SHOW EYE -->
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
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            <!-- HIDE EYE -->
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
                <div class="form-group">
                    <label for="password_confirmation">
                        Confirm New Password
                    </label>

                    <div class="password-input-wrapper">

                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            :type="
                                showConfirmation
                                    ? 'text'
                                    : 'password'
                            "
                            placeholder="Confirm new password"
                            autocomplete="new-password"
                        />

                        <button
                            type="button"
                            class="eye-button"
                            @click="
                                showConfirmation = !showConfirmation
                            "
                            :aria-label="
                                showConfirmation
                                    ? 'Hide confirmation password'
                                    : 'Show confirmation password'
                            "
                        >
                            <!-- SHOW EYE -->
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
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            <!-- HIDE EYE -->
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


                <!-- SUCCESS MESSAGE -->
                <div
                    v-if="successMessage"
                    class="success-message"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <path d="m9 12 2 2 4-4" />
                    </svg>

                    <span>{{ successMessage }}</span>
                </div>


                <!-- ERROR MESSAGE -->
                <div
                    v-if="errorMessage"
                    class="error-message"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>

                    <span>{{ errorMessage }}</span>
                </div>


                <!-- UPDATE BUTTON -->
                <button
                    type="submit"
                    class="update-button"
                    :disabled="loading"
                >
                    <span v-if="loading">
                        Updating...
                    </span>

                    <span v-else>
                        Update Password
                    </span>
                </button>

            </form>
        </div>
    </div>
</template>


<script setup>
import { ref, reactive } from 'vue';
import { updatePassword } from '../../services/auth';


/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = reactive({
    current_password: '',
    password: '',
    password_confirmation: ''
});


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
| MESSAGES
|--------------------------------------------------------------------------
*/

const successMessage = ref('');
const errorMessage = ref('');
const loading = ref(false);


/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

async function changePassword() {

    successMessage.value = '';
    errorMessage.value = '';

    /*
    |----------------------------------------------------------------------
    | VALIDATION
    |----------------------------------------------------------------------
    */

    if (!form.current_password) {
        errorMessage.value =
            'Please enter your current password.';
        return;
    }

    if (!form.password) {
        errorMessage.value =
            'Please enter a new password.';
        return;
    }

    if (form.password.length < 8) {
        errorMessage.value =
            'New password must be at least 8 characters.';
        return;
    }

    if (!form.password_confirmation) {
        errorMessage.value =
            'Please confirm your new password.';
        return;
    }

    if (form.password !== form.password_confirmation) {
        errorMessage.value =
            'New password and confirmation password do not match.';
        return;
    }


    /*
    |----------------------------------------------------------------------
    | LOADING
    |----------------------------------------------------------------------
    */

    loading.value = true;


    try {

        /*
        |------------------------------------------------------------------
        | API REQUEST
        |------------------------------------------------------------------
        */

        const response = await updatePassword({
            current_password: form.current_password,
            password: form.password,
            password_confirmation: form.password_confirmation
        });


        /*
        |------------------------------------------------------------------
        | SUCCESS
        |------------------------------------------------------------------
        */

        successMessage.value =
            response.data?.message ||
            'Password updated successfully.';


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

    } catch (error) {

        /*
        |------------------------------------------------------------------
        | VALIDATION ERRORS
        |------------------------------------------------------------------
        */

        if (error.response?.data?.errors) {

            const errors = error.response.data.errors;

            const firstError =
                Object.values(errors)[0];

            if (Array.isArray(firstError)) {
                errorMessage.value = firstError[0];
            } else {
                errorMessage.value = firstError;
            }

        } else {

            errorMessage.value =
                error.response?.data?.message ||
                'Unable to update password. Please try again.';
        }

    } finally {

        loading.value = false;

    }
}
</script>


<style scoped>

.page {
    width: 100%;
    padding: 30px;
    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.page-header {
    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.page-header p {
    margin: 7px 0 0;
    font-size: 14px;
    color: #6b7280;
}


/*
|--------------------------------------------------------------------------
| CARD
|--------------------------------------------------------------------------
*/

.password-card {
    width: 100%;
    max-width: 600px;

    padding: 30px;

    background: #ffffff;

    border-radius: 14px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.08);

    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| FORM GROUP
|--------------------------------------------------------------------------
*/

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    font-size: 14px;
    font-weight: 600;

    color: #374151;
}


/*
|--------------------------------------------------------------------------
| PASSWORD INPUT
|--------------------------------------------------------------------------
*/

.password-input-wrapper {
    position: relative;
    width: 100%;
}

.password-input-wrapper input {
    width: 100%;
    height: 48px;

    padding: 0 55px 0 14px;

    border: 1px solid #d1d5db;
    border-radius: 8px;

    outline: none;

    background: #ffffff;
    color: #111827;

    font-size: 14px;

    box-sizing: border-box;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.password-input-wrapper input::placeholder {
    color: #9ca3af;
}

.password-input-wrapper input:focus {
    border-color: #6366f1;

    box-shadow:
        0 0 0 3px rgba(99, 102, 241, 0.12);
}


/*
|--------------------------------------------------------------------------
| EYE BUTTON
|--------------------------------------------------------------------------
*/

.eye-button {
    position: absolute;

    right: 0;
    top: 0;

    width: 50px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 0;

    border: none;
    outline: none;

    background: transparent;

    color: #6b7280;

    cursor: pointer;

    z-index: 10;

    transition:
        color 0.2s ease,
        background 0.2s ease;
}

.eye-button:hover {
    color: #4f46e5;
}

.eye-button:focus {
    color: #4f46e5;
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
| SUCCESS
|--------------------------------------------------------------------------
*/

.success-message {
    display: flex;
    align-items: center;
    gap: 9px;

    margin-bottom: 18px;

    padding: 12px 14px;

    border-radius: 8px;

    background: #ecfdf5;
    border: 1px solid #a7f3d0;

    color: #047857;

    font-size: 14px;
}

.success-message svg {
    width: 19px;
    height: 19px;

    flex-shrink: 0;
}


/*
|--------------------------------------------------------------------------
| ERROR
|--------------------------------------------------------------------------
*/

.error-message {
    display: flex;
    align-items: center;
    gap: 9px;

    margin-bottom: 18px;

    padding: 12px 14px;

    border-radius: 8px;

    background: #fef2f2;
    border: 1px solid #fecaca;

    color: #dc2626;

    font-size: 14px;
}

.error-message svg {
    width: 19px;
    height: 19px;

    flex-shrink: 0;
}


/*
|--------------------------------------------------------------------------
| UPDATE BUTTON
|--------------------------------------------------------------------------
*/

.update-button {
    width: 100%;
    height: 48px;

    border: none;
    border-radius: 8px;

    background: #4f46e5;
    color: #ffffff;

    font-size: 15px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background 0.2s ease,
        opacity 0.2s ease;
}

.update-button:hover:not(:disabled) {
    background: #4338ca;
}

.update-button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}


/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .page {
        padding: 20px;
    }

    .password-card {
        padding: 22px;
    }

    .page-header h2 {
        font-size: 24px;
    }
}

</style>