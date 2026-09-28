<template>
    <div class="theme-page">

        <!-- HEADER -->
        <div class="page-header">
            <div>
                <span class="label">APPEARANCE</span>

                <h2>Theme Settings</h2>

                <p>
                    Customize the appearance of your admin panel.
                </p>
            </div>
        </div>


        <!-- SUCCESS -->
        <div
            v-if="success"
            class="success-alert"
        >
            <i class="fa-solid fa-circle-check"></i>

            <span>{{ success }}</span>
        </div>


        <!-- ERROR -->
        <div
            v-if="error"
            class="error-alert"
        >
            <i class="fa-solid fa-circle-exclamation"></i>

            <span>{{ error }}</span>
        </div>


        <!-- THEME CARD -->
        <div class="theme-card">

            <!-- TITLE -->
            <div class="card-title">

                <div>
                    <h3>Choose Theme</h3>

                    <p>
                        Select how the admin panel should look.
                    </p>
                </div>

                <i class="fa-solid fa-palette"></i>

            </div>


            <!-- THEME OPTIONS -->
            <div class="theme-options">

                <!-- LIGHT -->
                <button
                    type="button"
                    class="theme-option"
                    :class="{ selected: theme === 'light' }"
                    @click="selectTheme('light')"
                >
                    <div class="preview light-preview">
                        <div class="preview-sidebar"></div>

                        <div class="preview-content">
                            <div class="preview-header"></div>
                            <div class="preview-box"></div>
                            <div class="preview-box small"></div>
                        </div>
                    </div>

                    <div class="option-info">
                        <div>
                            <strong>Light</strong>
                            <span>Clean and bright interface</span>
                        </div>

                        <div class="radio">
                            <i
                                v-if="theme === 'light'"
                                class="fa-solid fa-check"
                            ></i>
                        </div>
                    </div>
                </button>


                <!-- DARK -->
                <button
                    type="button"
                    class="theme-option"
                    :class="{ selected: theme === 'dark' }"
                    @click="selectTheme('dark')"
                >
                    <div class="preview dark-preview">
                        <div class="preview-sidebar"></div>

                        <div class="preview-content">
                            <div class="preview-header"></div>
                            <div class="preview-box"></div>
                            <div class="preview-box small"></div>
                        </div>
                    </div>

                    <div class="option-info">
                        <div>
                            <strong>Dark</strong>
                            <span>Dark interface for low-light use</span>
                        </div>

                        <div class="radio">
                            <i
                                v-if="theme === 'dark'"
                                class="fa-solid fa-check"
                            ></i>
                        </div>
                    </div>
                </button>


                <!-- SYSTEM -->
                <button
                    type="button"
                    class="theme-option"
                    :class="{ selected: theme === 'system' }"
                    @click="selectTheme('system')"
                >
                    <div class="preview system-preview">
                        <div class="system-half light-half"></div>
                        <div class="system-half dark-half"></div>
                    </div>

                    <div class="option-info">
                        <div>
                            <strong>System</strong>
                            <span>Follow your device preference</span>
                        </div>

                        <div class="radio">
                            <i
                                v-if="theme === 'system'"
                                class="fa-solid fa-check"
                            ></i>
                        </div>
                    </div>
                </button>

            </div>


            <!-- SAVE -->
            <div class="save-area">
                <button
                    type="button"
                    class="save-btn"
                    :disabled="saving"
                    @click="saveTheme"
                >
                    <span v-if="saving" class="spinner"></span>

                    <i
                        v-else
                        class="fa-solid fa-floppy-disk"
                    ></i>

                    {{ saving ? 'Saving...' : 'Save Theme' }}
                </button>
            </div>

        </div>

    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';

import { useTheme } from '../../composables/useTheme';

import {
    getAdminProfile,
    updateAdminTheme,
} from '../../services/admin';


/*
|--------------------------------------------------------------------------
| THEME COMPOSABLE
|--------------------------------------------------------------------------
*/

const { theme, setTheme } = useTheme();


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const saving = ref(false);
const success = ref('');
const error = ref('');

const validThemes = ['light', 'dark', 'system'];


/*
|--------------------------------------------------------------------------
| SELECT THEME
|--------------------------------------------------------------------------
*/

function selectTheme(value) {
    if (!validThemes.includes(value)) {
        return;
    }

    success.value = '';
    error.value = '';

    setTheme(value);
}


/*
|--------------------------------------------------------------------------
| LOAD THEME
|--------------------------------------------------------------------------
*/

async function loadTheme() {
    try {
        const response = await getAdminProfile();

        const data = response?.data || {};

        const user =
            data.user ||
            data.admin ||
            data.profile ||
            data;

        if (user?.theme && validThemes.includes(user.theme)) {
            setTheme(user.theme);
        }
    } catch (err) {
        console.error('Theme load error:', err);
    }
}


/*
|--------------------------------------------------------------------------
| SAVE THEME
|--------------------------------------------------------------------------
*/

async function saveTheme() {
    if (saving.value) {
        return;
    }

    saving.value = true;
    success.value = '';
    error.value = '';

    try {
        setTheme(theme.value);

        await updateAdminTheme({ theme: theme.value });

        success.value = 'Theme updated successfully.';
    } catch (err) {
        console.error('Theme save error:', err);

        error.value =
            err?.response?.data?.message ||
            err?.response?.data?.error ||
            'Unable to update theme.';
    } finally {
        saving.value = false;
    }
}


/*
|--------------------------------------------------------------------------
| MOUNTED
|--------------------------------------------------------------------------
*/

onMounted(() => {
    loadTheme();
});
</script>


<style scoped>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.theme-page {
    width: 100%;
    min-height: 100%;
    color: #111827;
}

/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

.page-header {
    margin-bottom: 22px;
}

.label {
    display: inline-block;
    margin-bottom: 6px;
    color: #9ca3af;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.3px;
}

.page-header h2 {
    margin: 0;
    color: #111827;
    font-size: 25px;
    font-weight: 750;
}

.page-header p {
    margin: 5px 0 0;
    color: #9ca3af;
    font-size: 13px;
}

/*
|--------------------------------------------------------------------------
| ALERTS
|--------------------------------------------------------------------------
*/

.success-alert,
.error-alert {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 12px 15px;
    margin-bottom: 18px;
    border-radius: 9px;
    font-size: 12px;
}

.success-alert {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
}

.error-alert {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

/*
|--------------------------------------------------------------------------
| CARD
|--------------------------------------------------------------------------
*/

.theme-card {
    padding: 24px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    box-shadow: 0 5px 18px rgba(0, 0, 0, 0.035);
}

/*
|--------------------------------------------------------------------------
| CARD TITLE
|--------------------------------------------------------------------------
*/

.card-title {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.card-title h3 {
    margin: 0;
    color: #111827;
    font-size: 16px;
    font-weight: 700;
}

.card-title p {
    margin: 4px 0 0;
    color: #9ca3af;
    font-size: 11px;
}

.card-title > i {
    color: #6b7280;
    font-size: 18px;
}

/*
|--------------------------------------------------------------------------
| OPTIONS
|--------------------------------------------------------------------------
*/

.theme-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding-top: 22px;
}

.theme-option {
    padding: 0;
    overflow: hidden;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    background: #ffffff;
    color: #111827;
    text-align: left;
    cursor: pointer;
    transition: 0.2s ease;
}

.theme-option:hover {
    border-color: #9ca3af;
    transform: translateY(-2px);
}

.theme-option.selected {
    border-color: #111827;
    box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.06);
}

/*
|--------------------------------------------------------------------------
| PREVIEW
|--------------------------------------------------------------------------
*/

.preview {
    height: 145px;
    display: flex;
    overflow: hidden;
}

.light-preview {
    background: #f7f8fa;
}

.light-preview .preview-sidebar {
    width: 28%;
    background: #111827;
}

.light-preview .preview-content {
    flex: 1;
}

.light-preview .preview-header {
    height: 25px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.light-preview .preview-box {
    height: 37px;
    margin: 12px;
    border-radius: 5px;
    background: #ffffff;
}

.light-preview .preview-box.small {
    width: 65%;
}

.dark-preview {
    background: #1f2937;
}

.dark-preview .preview-sidebar {
    width: 28%;
    background: #111827;
}

.dark-preview .preview-content {
    flex: 1;
}

.dark-preview .preview-header {
    height: 25px;
    background: #374151;
}

.dark-preview .preview-box {
    height: 37px;
    margin: 12px;
    border-radius: 5px;
    background: #374151;
}

.dark-preview .preview-box.small {
    width: 65%;
}

.system-preview {
    background: #f3f4f6;
}

.system-half {
    width: 50%;
    height: 100%;
}

.light-half {
    background: #f9fafb;
}

.dark-half {
    background: #1f2937;
}

/*
|--------------------------------------------------------------------------
| OPTION INFO
|--------------------------------------------------------------------------
*/

.option-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px;
}

.option-info strong {
    display: block;
    color: #111827;
    font-size: 12px;
}

.option-info span {
    display: block;
    margin-top: 3px;
    color: #9ca3af;
    font-size: 9px;
}

/*
|--------------------------------------------------------------------------
| RADIO
|--------------------------------------------------------------------------
*/

.radio {
    width: 23px;
    height: 23px;
    min-width: 23px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d1d5db;
    border-radius: 50%;
    color: #ffffff;
    font-size: 10px;
}

.selected .radio {
    background: #111827;
    border-color: #111827;
}

/*
|--------------------------------------------------------------------------
| SAVE AREA
|--------------------------------------------------------------------------
*/

.save-area {
    display: flex;
    justify-content: flex-end;
    padding-top: 22px;
    margin-top: 22px;
    border-top: 1px solid #f0f0f0;
}

.save-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 40px;
    padding: 0 17px;
    border: 0;
    border-radius: 8px;
    background: #111827;
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s ease;
}

.save-btn:hover {
    background: #1f2937;
}

.save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/*
|--------------------------------------------------------------------------
| SPINNER
|--------------------------------------------------------------------------
*/

.spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.35);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: theme-spin 0.7s linear infinite;
}

@keyframes theme-spin {
    to {
        transform: rotate(360deg);
    }
}

/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 900px) {
    .theme-options {
        grid-template-columns: 1fr;
    }

    .preview {
        height: 130px;
    }
}

@media (max-width: 600px) {
    .theme-card {
        padding: 17px;
    }

    .page-header h2 {
        font-size: 21px;
    }
}

</style>