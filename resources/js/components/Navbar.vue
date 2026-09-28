<template>
    <header class="navbar">
        <router-link to="/" class="navbar-brand">
            Brand<span>X</span>
        </router-link>

        <nav class="navbar-links">
            <router-link to="/" class="navbar-link">
                Home
            </router-link>

            <router-link to="/about" class="navbar-link">
                About
            </router-link>

            <router-link to="/contact" class="navbar-link">
                Contact
            </router-link>
        </nav>

        <div class="navbar-actions">
            <template v-if="isAuth">
                <router-link
                    to="/dashboard"
                    class="navbar-btn primary"
                >
                    Dashboard
                </router-link>
            </template>

            <template v-else>
                <router-link to="/login" class="navbar-btn">
                    Login
                </router-link>

                <router-link
                    to="/register"
                    class="navbar-btn primary"
                >
                    Register
                </router-link>
            </template>
        </div>
    </header>
</template>

<script setup>
import { computed } from 'vue';
import { authState } from '../stores/auth';

const isAuth = computed(() => !!authState.user);
</script>

<style scoped>
.navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    height: 68px;
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    position: sticky;
    top: 0;
    z-index: 900;
}

.navbar-brand {
    font-size: 22px;
    font-weight: 800;
    color: #111827;
    text-decoration: none;
}

.navbar-brand span {
    color: #2563eb;
}

.navbar-links {
    display: flex;
    gap: 20px;
}

.navbar-link {
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.navbar-link:hover,
.navbar-link.router-link-active {
    color: #2563eb;
}

.navbar-actions {
    display: flex;
    gap: 10px;
}

.navbar-btn {
    padding: 9px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    color: #2563eb;
    border: 1px solid #2563eb;
}

.navbar-btn.primary {
    background: #2563eb;
    color: white;
}

.navbar-btn:hover {
    opacity: 0.9;
}

html.dark .navbar {
    background: #111827;
    border-color: #1f2937;
}

html.dark .navbar-brand {
    color: #f9fafb;
}

html.dark .navbar-link {
    color: #cbd5e1;
}
</style>