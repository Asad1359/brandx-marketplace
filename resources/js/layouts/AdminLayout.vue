<template>
    <div class="admin-layout">

        <!-- MOBILE OVERLAY -->
        <div
            v-if="sidebarOpen"
            class="sidebar-overlay"
            @click="sidebarOpen = false"
        ></div>


        <!-- SIDEBAR -->
        <aside
            class="admin-sidebar"
            :class="{ 'sidebar-open': sidebarOpen }"
        >

            <!-- BRAND -->
            <div class="brand">
                <div class="brand-name">
                    BrandX
                </div>

                <div class="brand-label">
                    Admin Panel
                </div>
            </div>


            <!-- ADMIN PROFILE -->
            <router-link
                to="/admin/profile"
                class="admin-mini-profile"
                @click="sidebarOpen = false"
            >

                <div class="admin-avatar">
                    {{ getInitial(adminName) }}
                </div>

                <div class="admin-info">
                    <strong>{{ adminName }}</strong>
                    <span>Administrator</span>
                </div>

            </router-link>


            <!-- MAIN NAVIGATION -->
            <div class="nav-section">

                <div class="nav-title">
                    MAIN
                </div>


                <!-- DASHBOARD -->
                <router-link
                    to="/admin/dashboard"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/dashboard')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </router-link>


                <!-- USERS -->
                <router-link
                    to="/admin/users"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/users')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </router-link>


                <!-- BAGS -->
                <router-link
                    to="/admin/bags"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/bags')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Bags</span>
                </router-link>


                <!-- ADD BAG -->
                <router-link
                    to="/admin/bags/create"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/bags/create')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Bag</span>
                </router-link>

            </div>


            <!-- ACCOUNT -->
            <div class="nav-section">

                <div class="nav-title">
                    ACCOUNT
                </div>


                <!-- PROFILE -->
                <router-link
                    to="/admin/profile"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/profile')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-user"></i>
                    <span>Profile</span>
                </router-link>


                <!-- PASSWORD -->
                <router-link
                    to="/admin/change-password"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/change-password')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-lock"></i>
                    <span>Password</span>
                </router-link>


                <!-- THEME -->
                <router-link
                    to="/admin/theme"
                    class="nav-item"
                    :class="{
                        active: isActive('/admin/theme')
                    }"
                    @click="sidebarOpen = false"
                >
                    <i class="fa-solid fa-palette"></i>
                    <span>Theme</span>
                </router-link>

            </div>


            <!-- LOGOUT -->
            <button
                type="button"
                class="logout-btn"
                @click="handleLogout"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </button>

        </aside>


        <!-- RIGHT SIDE -->
        <div class="admin-main">

            <!-- HEADER -->
            <header class="admin-header">

                <!-- MOBILE MENU -->
                <button
                    type="button"
                    class="mobile-menu"
                    @click="sidebarOpen = true"
                >
                    <i class="fa-solid fa-bars"></i>
                </button>


                <!-- HEADER LEFT -->
                <div class="header-left">

                    <h1>
                        Admin Dashboard
                    </h1>

                    <p>
                        Manage your website from one place
                    </p>

                </div>


                <!-- HEADER PROFILE -->
                <router-link
                    to="/admin/profile"
                    class="header-profile"
                >

                    <div class="header-avatar">
                        {{ getInitial(adminName) }}
                    </div>

                    <div class="header-user">

                        <strong>
                            {{ adminName }}
                        </strong>

                        <span>
                            Admin
                        </span>

                    </div>

                </router-link>

            </header>


            <!-- CHILD ROUTES -->
            <main class="admin-content">
                <router-view />
            </main>

        </div>

    </div>
</template>


<script setup>
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import {
    authState,
    logout
} from '../stores/auth';


const router = useRouter();
const route = useRoute();

const sidebarOpen = ref(false);


/*
|--------------------------------------------------------------------------
| ADMIN NAME
|--------------------------------------------------------------------------
*/

const adminName = computed(() => {
    return authState.user?.name || 'Admin';
});


/*
|--------------------------------------------------------------------------
| GET INITIAL
|--------------------------------------------------------------------------
*/

function getInitial(name) {
    return String(name || 'A')
        .charAt(0)
        .toUpperCase();
}


/*
|--------------------------------------------------------------------------
| ACTIVE NAVIGATION
|--------------------------------------------------------------------------
*/

function isActive(path) {

    /*
    |--------------------------------------------------------------------------
    | Bags main link should NOT stay active on /bags/create
    |--------------------------------------------------------------------------
    */

    if (path === '/admin/bags') {
        return route.path === '/admin/bags';
    }


    return (
        route.path === path ||
        route.path.startsWith(path + '/')
    );
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

async function handleLogout() {

    try {
        await logout();
    } catch (error) {
        console.error('Logout error:', error);
    } finally {

        router.push({
            name: 'login'
        });

    }
}
</script>


<style scoped>

* {
    box-sizing: border-box;
}


/* =========================================================
   ADMIN LAYOUT
========================================================= */

.admin-layout {
    min-height: 100vh;

    display: flex;

    background: #f7f8fa;
}


/* =========================================================
   SIDEBAR
========================================================= */

.admin-sidebar {
    position: fixed;

    top: 0;
    left: 0;
    bottom: 0;

    width: 260px;

    z-index: 1000;

    display: flex;
    flex-direction: column;

    padding: 24px 16px;

    background: #111827;

    color: white;

    overflow-y: auto;
}


/* =========================================================
   BRAND
========================================================= */

.brand {
    padding: 0 12px 25px;

    margin-bottom: 20px;

    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-name {
    font-size: 21px;

    font-weight: 800;

    letter-spacing: -0.4px;
}

.brand-label {
    margin-top: 3px;

    color: #9ca3af;

    font-size: 11px;
}


/* =========================================================
   ADMIN PROFILE
========================================================= */

.admin-mini-profile {
    display: flex;

    align-items: center;

    gap: 11px;

    padding: 10px 11px;

    margin-bottom: 24px;

    border-radius: 10px;

    text-decoration: none;

    color: white;

    background: rgba(255, 255, 255, 0.05);

    transition: 0.18s ease;
}

.admin-mini-profile:hover {
    background: rgba(255, 255, 255, 0.09);
}

.admin-avatar {
    width: 38px;
    height: 38px;

    min-width: 38px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: #374151;

    color: white;

    font-size: 13px;

    font-weight: 700;
}

.admin-info {
    min-width: 0;
}

.admin-info strong {
    display: block;

    color: white;

    font-size: 12px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.admin-info span {
    display: block;

    margin-top: 2px;

    color: #9ca3af;

    font-size: 10px;
}


/* =========================================================
   NAVIGATION
========================================================= */

.nav-section {
    margin-bottom: 22px;
}

.nav-title {
    padding: 0 12px;

    margin-bottom: 8px;

    color: #6b7280;

    font-size: 9px;

    font-weight: 800;

    letter-spacing: 1.3px;
}

.nav-item {
    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 40px;

    padding: 0 12px;

    margin-bottom: 3px;

    border-radius: 8px;

    color: #9ca3af;

    text-decoration: none;

    font-size: 12px;

    transition: 0.18s ease;
}

.nav-item i {
    width: 17px;

    text-align: center;

    font-size: 12px;
}

.nav-item:hover {
    color: white;

    background: rgba(255, 255, 255, 0.06);
}

.nav-item.active {
    color: white;

    background: rgba(255, 255, 255, 0.1);

    font-weight: 600;
}


/* =========================================================
   LOGOUT
========================================================= */

.logout-btn {
    width: 100%;

    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 40px;

    padding: 0 12px;

    margin-top: auto;

    border: 0;

    border-radius: 8px;

    background: transparent;

    color: #9ca3af;

    font-size: 12px;

    cursor: pointer;

    text-align: left;

    transition: 0.18s ease;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.06);

    color: white;
}

.logout-btn i {
    width: 17px;

    text-align: center;
}


/* =========================================================
   MAIN AREA
========================================================= */

.admin-main {
    width: calc(100% - 260px);

    min-height: 100vh;

    margin-left: 260px;
}


/* =========================================================
   HEADER
========================================================= */

.admin-header {
    height: 78px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 32px;

    background: white;

    border-bottom: 1px solid #e5e7eb;

    position: sticky;

    top: 0;

    z-index: 100;
}

.header-left h1 {
    margin: 0;

    color: #111827;

    font-size: 17px;

    font-weight: 750;
}

.header-left p {
    margin: 3px 0 0;

    color: #9ca3af;

    font-size: 11px;
}

.header-profile {
    display: flex;

    align-items: center;

    gap: 9px;

    text-decoration: none;

    color: inherit;
}

.header-avatar {
    width: 35px;
    height: 35px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: #f3f4f6;

    color: #374151;

    font-size: 12px;

    font-weight: 700;
}

.header-user strong {
    display: block;

    color: #111827;

    font-size: 11px;
}

.header-user span {
    display: block;

    margin-top: 1px;

    color: #9ca3af;

    font-size: 9px;
}


/* =========================================================
   CONTENT
========================================================= */

.admin-content {
    width: 100%;

    min-height: calc(100vh - 78px);

    padding: 30px 32px;

    background: #f7f8fa;
}


/* =========================================================
   MOBILE MENU
========================================================= */

.mobile-menu {
    display: none;

    width: 36px;
    height: 36px;

    margin-right: 12px;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    background: white;

    color: #374151;

    cursor: pointer;
}


/* =========================================================
   SIDEBAR OVERLAY
========================================================= */

.sidebar-overlay {
    display: none;
}


/* =========================================================
   TABLET / MOBILE
========================================================= */

@media (max-width: 900px) {

    .admin-sidebar {
        transform: translateX(-100%);

        transition: transform 0.25s ease;
    }


    .admin-sidebar.sidebar-open {
        transform: translateX(0);
    }


    .admin-main {
        width: 100%;

        margin-left: 0;
    }


    .mobile-menu {
        display: block;
    }


    .admin-header {
        padding: 0 18px;
    }


    .admin-content {
        padding: 22px 18px;
    }


    .sidebar-overlay {
        position: fixed;

        inset: 0;

        z-index: 999;

        display: block;

        background: rgba(0, 0, 0, 0.45);
    }


    .header-user {
        display: none;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 600px) {

    .header-left h1 {
        font-size: 15px;
    }


    .header-left p {
        display: none;
    }


    .admin-content {
        padding: 18px 14px;
    }

}

</style>