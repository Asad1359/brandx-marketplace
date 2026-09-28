<template>
    <div class="page">

        <div class="page-header">
            <div>
                <h2>Theme Settings</h2>
                <p>Customize the appearance of your dashboard.</p>
            </div>
        </div>

        <div class="card">

            <h3>Appearance</h3>
            <p class="description">
                Select your preferred dashboard theme.
            </p>

            <div class="theme-options">

                <button
                    class="theme-option"
                    :class="{ selected: theme === 'light' }"
                    @click="setTheme('light')"
                >
                    <div class="theme-icon light-icon">
                        <i class="fa-solid fa-sun"></i>
                    </div>

                    <div>
                        <strong>Light</strong>
                        <span>Use the light appearance</span>
                    </div>

                    <i
                        v-if="theme === 'light'"
                        class="fa-solid fa-circle-check check"
                    ></i>
                </button>

                <button
                    class="theme-option"
                    :class="{ selected: theme === 'dark' }"
                    @click="setTheme('dark')"
                >
                    <div class="theme-icon dark-icon">
                        <i class="fa-solid fa-moon"></i>
                    </div>

                    <div>
                        <strong>Dark</strong>
                        <span>Use the dark appearance</span>
                    </div>

                    <i
                        v-if="theme === 'dark'"
                        class="fa-solid fa-circle-check check"
                    ></i>
                </button>

                <button
                    class="theme-option"
                    :class="{ selected: theme === 'system' }"
                    @click="setTheme('system')"
                >
                    <div class="theme-icon system-icon">
                        <i class="fa-solid fa-desktop"></i>
                    </div>

                    <div>
                        <strong>System</strong>
                        <span>Follow your device settings</span>
                    </div>

                    <i
                        v-if="theme === 'system'"
                        class="fa-solid fa-circle-check check"
                    ></i>
                </button>

            </div>

        </div>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const theme = ref('light');

onMounted(() => {
    const savedTheme = localStorage.getItem('user-theme');

    if (savedTheme) {
        theme.value = savedTheme;
    }
});

function setTheme(value) {
    theme.value = value;

    localStorage.setItem('user-theme', value);

    if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else if (value === 'light') {
        document.documentElement.classList.remove('dark');
    } else {
        const darkMode = window.matchMedia(
            '(prefers-color-scheme: dark)'
        ).matches;

        document.documentElement.classList.toggle(
            'dark',
            darkMode
        );
    }
}
</script>

<style scoped>
.page {
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;
    color: #111827;
    font-size: 24px;
}

.page-header p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 15px;
    padding: 28px;
}

.card h3 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.description {
    margin: 6px 0 25px;
    color: #6b7280;
    font-size: 13px;
}

.theme-options {
    display: grid;
    gap: 12px;
}

.theme-option {
    width: 100%;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 12px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-align: left;
    cursor: pointer;
    transition: .2s;
}

.theme-option:hover {
    border-color: #2563eb;
}

.theme-option.selected {
    border-color: #2563eb;
    background: #eff6ff;
}

.theme-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.light-icon {
    background: #fef3c7;
    color: #d97706;
}

.dark-icon {
    background: #e5e7eb;
    color: #374151;
}

.system-icon {
    background: #dbeafe;
    color: #2563eb;
}

.theme-option strong {
    display: block;
    color: #111827;
    font-size: 14px;
}

.theme-option span {
    display: block;
    color: #6b7280;
    font-size: 12px;
    margin-top: 3px;
}

.check {
    margin-left: auto;
    color: #2563eb;
    font-size: 18px;
}
</style>