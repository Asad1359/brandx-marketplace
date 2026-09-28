<template>
    <div class="public-layout">

        <!-- =========================================
             NAVBAR
        ========================================== -->
        <header class="public-header">

            <nav class="navbar">

                <!-- BRAND -->
                <router-link
                    to="/"
                    class="brand"
                >
                    <span class="brand-name">
                        BrandX
                    </span>
                </router-link>


                <!-- DESKTOP NAVIGATION -->
                <div class="nav-links">

                    <router-link
                        to="/"
                        class="nav-link"
                        :class="{ active: isActive('/') }"
                    >
                        <i class="fa-solid fa-house"></i>
                        <span>Home</span>
                    </router-link>


                    <router-link
                        to="/about"
                        class="nav-link"
                        :class="{ active: isActive('/about') }"
                    >
                        <i class="fa-solid fa-circle-info"></i>
                        <span>About</span>
                    </router-link>


                    <router-link
                        to="/contact"
                        class="nav-link"
                        :class="{ active: isActive('/contact') }"
                    >
                        <i class="fa-solid fa-envelope"></i>
                        <span>Contact</span>
                    </router-link>


                    <!-- LOGGED IN USER -->
                    <template v-if="isAuthenticated">

                        <router-link
                            to="/user/dashboard"
                            class="nav-link"
                            :class="{
                                active: isActive('/user/dashboard')
                            }"
                        >
                            <i class="fa-solid fa-gauge"></i>
                            <span>Dashboard</span>
                        </router-link>

                    </template>


                    <!-- GUEST -->
                    <template v-else>

                        <router-link
                            to="/login"
                            class="nav-link"
                            :class="{ active: isActive('/login') }"
                        >
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Login</span>
                        </router-link>


                        <router-link
                            to="/register"
                            class="register-btn"
                            :class="{ active: isActive('/register') }"
                        >
                            Register
                        </router-link>

                    </template>

                </div>


                <!-- MOBILE MENU BUTTON -->
                <button
                    type="button"
                    class="mobile-menu-btn"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle navigation"
                >
                    <i
                        :class="
                            mobileMenuOpen
                                ? 'fa-solid fa-xmark'
                                : 'fa-solid fa-bars'
                        "
                    ></i>
                </button>

            </nav>


            <!-- MOBILE NAVIGATION -->
            <div
                v-if="mobileMenuOpen"
                class="mobile-nav"
            >

                <router-link
                    to="/"
                    class="mobile-nav-link"
                    :class="{ active: isActive('/') }"
                    @click="closeMobileMenu"
                >
                    <i class="fa-solid fa-house"></i>
                    <span>Home</span>
                </router-link>


                <router-link
                    to="/about"
                    class="mobile-nav-link"
                    :class="{ active: isActive('/about') }"
                    @click="closeMobileMenu"
                >
                    <i class="fa-solid fa-circle-info"></i>
                    <span>About</span>
                </router-link>


                <router-link
                    to="/contact"
                    class="mobile-nav-link"
                    :class="{ active: isActive('/contact') }"
                    @click="closeMobileMenu"
                >
                    <i class="fa-solid fa-envelope"></i>
                    <span>Contact</span>
                </router-link>


                <!-- AUTHENTICATED -->
                <template v-if="isAuthenticated">

                    <router-link
                        to="/user/dashboard"
                        class="mobile-nav-link"
                        :class="{
                            active: isActive('/user/dashboard')
                        }"
                        @click="closeMobileMenu"
                    >
                        <i class="fa-solid fa-gauge"></i>
                        <span>Dashboard</span>
                    </router-link>

                </template>


                <!-- GUEST -->
                <template v-else>

                    <router-link
                        to="/login"
                        class="mobile-nav-link"
                        :class="{ active: isActive('/login') }"
                        @click="closeMobileMenu"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Login</span>
                    </router-link>


                    <router-link
                        to="/register"
                        class="mobile-register-btn"
                        :class="{ active: isActive('/register') }"
                        @click="closeMobileMenu"
                    >
                        Register
                    </router-link>

                </template>

            </div>

        </header>


        <!-- =========================================
             MAIN CONTENT
        ========================================== -->
        <main class="public-content">

            <router-view />

        </main>


        <!-- =========================================
             FOOTER
        ========================================== -->
        <footer class="public-footer">

            <div class="footer-container">

                <!-- BRAND -->
                <div class="footer-brand">

                    <h3>
                        BrandX
                    </h3>

                    <p>
                        Simple, modern and powerful product
                        management platform.
                    </p>

                </div>


                <!-- QUICK LINKS -->
                <div class="footer-links">

                    <h5>
                        Quick Links
                    </h5>

                    <router-link to="/">
                        Home
                    </router-link>

                    <router-link to="/about">
                        About
                    </router-link>

                    <router-link to="/contact">
                        Contact
                    </router-link>

                </div>


                <!-- ACCOUNT -->
                <div class="footer-links">

                    <h5>
                        Account
                    </h5>

                    <template v-if="isAuthenticated">

                        <router-link to="/user/dashboard">
                            Dashboard
                        </router-link>

                    </template>

                    <template v-else>

                        <router-link to="/login">
                            Login
                        </router-link>

                        <router-link to="/register">
                            Register
                        </router-link>

                    </template>

                </div>

            </div>


            <!-- COPYRIGHT -->
            <div class="footer-bottom">

                <p>
                    © {{ currentYear }} BrandX.
                    All rights reserved.
                </p>

            </div>

        </footer>

    </div>
</template>


<script setup>
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';

import {
    authState
} from '../stores/auth';


const route = useRoute();

const mobileMenuOpen = ref(false);


/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

const isAuthenticated = computed(() => {
    return !!authState.user;
});


/*
|--------------------------------------------------------------------------
| CURRENT YEAR
|--------------------------------------------------------------------------
*/

const currentYear = new Date().getFullYear();


/*
|--------------------------------------------------------------------------
| ACTIVE ROUTE
|--------------------------------------------------------------------------
*/

function isActive(path) {

    /*
    |--------------------------------------------------------------------------
    | Home
    |--------------------------------------------------------------------------
    */

    if (path === '/') {
        return route.path === '/';
    }


    return (
        route.path === path ||
        route.path.startsWith(path + '/')
    );
}


/*
|--------------------------------------------------------------------------
| CLOSE MOBILE MENU
|--------------------------------------------------------------------------
*/

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}
</script>


<style scoped>

* {
    box-sizing: border-box;
}


/* =========================================================
   LAYOUT
========================================================= */

.public-layout {
    min-height: 100vh;

    display: flex;

    flex-direction: column;

    background: #f8f9fa;

    color: #212529;
}


/* =========================================================
   HEADER
========================================================= */

.public-header {
    position: sticky;

    top: 0;

    z-index: 1000;

    background: #ffffff;

    border-bottom: 1px solid #e9ecef;
}


/* =========================================================
   NAVBAR
========================================================= */

.navbar {
    width: 100%;

    min-height: 70px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 40px;
}


/* =========================================================
   BRAND
========================================================= */

.brand {
    display: inline-flex;

    align-items: center;

    text-decoration: none;
}

.brand-name {
    color: #111827;

    font-size: 23px;

    font-weight: 800;

    letter-spacing: -0.5px;
}


/* =========================================================
   NAV LINKS
========================================================= */

.nav-links {
    display: flex;

    align-items: center;

    gap: 6px;
}

.nav-link {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border-radius: 8px;

    color: #6b7280;

    text-decoration: none;

    font-size: 13px;

    font-weight: 500;

    transition: 0.18s ease;
}

.nav-link i {
    font-size: 12px;
}

.nav-link:hover {
    color: #111827;

    background: #f3f4f6;
}

.nav-link.active {
    color: #111827;

    background: #f3f4f6;

    font-weight: 600;
}


/* =========================================================
   REGISTER BUTTON
========================================================= */

.register-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 9px 17px;

    margin-left: 5px;

    border-radius: 8px;

    background: #0d6efd;

    color: white;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: 0.18s ease;
}

.register-btn:hover {
    background: #0b5ed7;

    color: white;
}

.register-btn.active {
    background: #0b5ed7;
}


/* =========================================================
   MOBILE BUTTON
========================================================= */

.mobile-menu-btn {
    display: none;

    width: 40px;

    height: 40px;

    align-items: center;

    justify-content: center;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    background: white;

    color: #374151;

    font-size: 16px;

    cursor: pointer;
}


/* =========================================================
   MOBILE NAV
========================================================= */

.mobile-nav {
    display: none;

    padding: 10px 18px 18px;

    border-top: 1px solid #f1f1f1;

    background: white;
}

.mobile-nav-link {
    display: flex;

    align-items: center;

    gap: 10px;

    padding: 12px;

    margin-top: 4px;

    border-radius: 8px;

    color: #6b7280;

    text-decoration: none;

    font-size: 13px;
}

.mobile-nav-link:hover {
    background: #f3f4f6;

    color: #111827;
}

.mobile-nav-link.active {
    background: #f3f4f6;

    color: #111827;

    font-weight: 600;
}

.mobile-register-btn {
    display: flex;

    align-items: center;

    justify-content: center;

    margin-top: 10px;

    padding: 11px;

    border-radius: 8px;

    background: #0d6efd;

    color: white;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;
}


/* =========================================================
   CONTENT
========================================================= */

.public-content {
    width: 100%;

    flex: 1;

    min-height: calc(100vh - 70px);

    background: #f8f9fa;
}


/* =========================================================
   FOOTER
========================================================= */

.public-footer {
    background: #111827;

    color: white;

    margin-top: auto;
}

.footer-container {
    max-width: 1200px;

    margin: 0 auto;

    padding: 45px 30px;

    display: grid;

    grid-template-columns:
        2fr
        1fr
        1fr;

    gap: 40px;
}


/* FOOTER BRAND */

.footer-brand h3 {
    margin: 0 0 10px;

    font-size: 21px;

    font-weight: 800;
}

.footer-brand p {
    max-width: 360px;

    margin: 0;

    color: #9ca3af;

    font-size: 13px;

    line-height: 1.7;
}


/* FOOTER LINKS */

.footer-links {
    display: flex;

    flex-direction: column;

    gap: 9px;
}

.footer-links h5 {
    margin: 0 0 7px;

    color: white;

    font-size: 13px;

    font-weight: 700;
}

.footer-links a {
    color: #9ca3af;

    text-decoration: none;

    font-size: 12px;

    transition: 0.18s ease;
}

.footer-links a:hover {
    color: white;
}


/* =========================================================
   FOOTER BOTTOM
========================================================= */

.footer-bottom {
    padding: 17px 25px;

    border-top: 1px solid rgba(255, 255, 255, 0.08);

    text-align: center;
}

.footer-bottom p {
    margin: 0;

    color: #6b7280;

    font-size: 11px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 900px) {

    .navbar {
        padding: 0 20px;
    }

    .nav-links {
        display: none;
    }

    .mobile-menu-btn {
        display: inline-flex;
    }

    .mobile-nav {
        display: block;
    }

    .footer-container {
        grid-template-columns:
            1fr
            1fr;

        gap: 30px;
    }

    .footer-brand {
        grid-column: 1 / -1;
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .navbar {
        min-height: 62px;

        padding: 0 15px;
    }

    .brand-name {
        font-size: 20px;
    }

    .public-content {
        min-height: calc(100vh - 62px);
    }

    .footer-container {
        grid-template-columns: 1fr;

        padding: 35px 20px;

        gap: 28px;
    }

    .footer-brand {
        grid-column: auto;
    }

}

</style>
