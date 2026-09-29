<template>
    <div class="admin-dashboard">

        <!-- TOP HEADER -->
        <div class="dashboard-header">

            <div class="header-content">
                <div class="dashboard-label">
                    ADMIN PANEL
                </div>

                <h1>ADMIN DASHBOARD</h1>

                <p>
                    Welcome back, {{ userName }}. Manage users, products,
                    customer chats and your admin account.
                </p>
            </div>

            <div class="admin-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Administrator</span>
            </div>

        </div>


        <!-- ERROR -->
        <div
            v-if="error"
            class="error-message"
        >
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ error }}</span>
        </div>


        <!-- ADMIN ACCOUNT -->
        <section class="account-card">

            <div class="section-title">

                <div class="title-icon">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

                <div>
                    <h2>Admin Account</h2>

                    <p>
                        Your administrator account information
                    </p>
                </div>

            </div>


            <div class="account-content">

                <!-- Avatar -->
                <div class="profile-avatar">
                    <span>
                        {{ userInitial }}
                    </span>
                </div>


                <!-- Information -->
                <div class="account-info">

                    <h3>
                        {{ userName }}
                    </h3>

                    <p>
                        <i class="fa-solid fa-envelope"></i>
                        <span>{{ userEmail }}</span>
                    </p>

                    <p>
                        <i class="fa-solid fa-user-tag"></i>

                        <span>
                            Role:
                            <strong>{{ userRole }}</strong>
                        </span>
                    </p>

                </div>


                <!-- Profile Button -->
                <router-link
                    to="/admin/profile"
                    class="edit-profile-btn"
                >
                    <i class="fa-solid fa-pen"></i>

                    <span>
                        Edit Profile
                    </span>
                </router-link>

            </div>

        </section>


        <!-- QUICK ACTIONS -->
        <section class="quick-section">

            <div class="section-heading">

                <div>
                    <span class="section-label">
                        CONTROL CENTER
                    </span>

                    <h2>
                        Quick Actions
                    </h2>
                </div>

                <div class="heading-line"></div>

            </div>


            <div class="quick-grid">

                <!-- PRODUCTS -->
                <router-link
                    to="/admin/bags"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-box-open"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            Products
                        </h3>

                        <p>
                            Manage your products and inventory
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>


                <!-- USERS -->
                <router-link
                    to="/admin/users"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            User Management
                        </h3>

                        <p>
                            View and manage registered users
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>


                <!-- CUSTOMER CHAT -->
                <router-link
                    to="/admin/chats"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-comments"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            Customer Chat
                        </h3>

                        <p>
                            Chat with your customers
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>


                <!-- PROFILE -->
                <router-link
                    to="/admin/profile"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            Admin Profile
                        </h3>

                        <p>
                            Update your profile information
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>


                <!-- PASSWORD -->
                <router-link
                    to="/admin/change-password"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            Change Password
                        </h3>

                        <p>
                            Secure your administrator account
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>


                <!-- THEME -->
                <router-link
                    to="/admin/theme"
                    class="quick-card"
                >

                    <div class="quick-icon">
                        <i class="fa-solid fa-palette"></i>
                    </div>

                    <div class="quick-text">

                        <h3>
                            Theme Settings
                        </h3>

                        <p>
                            Customize your dashboard appearance
                        </p>

                    </div>

                    <i class="fa-solid fa-arrow-right arrow"></i>

                </router-link>

            </div>

        </section>


        <!-- ACCOUNT STATUS -->
        <section class="status-card">

            <div class="status-left">

                <div
                    class="status-icon"
                    :class="{ inactive: !isActive }"
                >

                    <i
                        v-if="isActive"
                        class="fa-solid fa-check"
                    ></i>

                    <i
                        v-else
                        class="fa-solid fa-xmark"
                    ></i>

                </div>


                <div class="status-content">

                    <span class="status-label">
                        ACCOUNT STATUS
                    </span>

                    <h3>
                        Admin Account is
                        {{ isActive ? 'Active' : 'Inactive' }}
                    </h3>

                    <p>
                        {{
                            isActive
                                ? 'Your administrator account is currently active.'
                                : 'Your administrator account is currently inactive.'
                        }}
                    </p>

                </div>

            </div>


            <div
                class="status-dot"
                :class="{ inactive: !isActive }"
            ></div>

        </section>

    </div>
</template>


<script setup>

import {
    computed,
    onMounted
} from 'vue';

import {
    authState,
    loadUser
} from '../../stores/auth';


/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/

const user = computed(() => {
    return authState.user;
});


/*
|--------------------------------------------------------------------------
| User Name
|--------------------------------------------------------------------------
*/

const userName = computed(() => {

    return user.value?.name || 'Administrator';

});


/*
|--------------------------------------------------------------------------
| User Email
|--------------------------------------------------------------------------
*/

const userEmail = computed(() => {

    return user.value?.email || 'No email available';

});


/*
|--------------------------------------------------------------------------
| User Role
|--------------------------------------------------------------------------
*/

const userRole = computed(() => {

    return user.value?.role || 'admin';

});


/*
|--------------------------------------------------------------------------
| User Initial
|--------------------------------------------------------------------------
*/

const userInitial = computed(() => {

    return userName.value
        .charAt(0)
        .toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Account Status
|--------------------------------------------------------------------------
*/

const isActive = computed(() => {

    return (
        user.value?.is_active === true ||
        user.value?.is_active === 1 ||
        user.value?.is_active === '1'
    );

});


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

const error = computed(() => {

    return authState.error || '';

});


/*
|--------------------------------------------------------------------------
| Load User
|--------------------------------------------------------------------------
*/

onMounted(async () => {

    try {

        await loadUser();

    } catch (err) {

        console.error(
            'Failed to load admin user:',
            err
        );

    }

});

</script>


<style scoped>

/* =========================================================
   MAIN
========================================================= */

.admin-dashboard {

    width: 100%;

    min-height: 100%;

    padding: 30px;

    box-sizing: border-box;

    background: transparent;

    color: #111827;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

}


/* =========================================================
   HEADER
========================================================= */

.dashboard-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 25px;

    margin-bottom: 30px;

}


.header-content {

    min-width: 0;

}


.dashboard-label {

    display: inline-block;

    margin-bottom: 8px;

    color: #4f46e5 !important;

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 3px;

}


.dashboard-header h1 {

    margin: 0;

    color: #111827 !important;

    font-size: 34px;

    font-weight: 900;

    letter-spacing: 2px;

    line-height: 1.2;

}


.dashboard-header p {

    margin: 10px 0 0;

    color: #4b5563 !important;

    font-size: 14px;

    line-height: 1.6;

}


/* =========================================================
   ADMIN BADGE
========================================================= */

.admin-badge {

    display: flex;

    align-items: center;

    gap: 9px;

    padding: 12px 18px;

    border: 1px solid rgba(99, 102, 241, 0.4);

    border-radius: 12px;

    background: rgba(79, 70, 229, 0.08);

    color: #4338ca !important;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

}


.admin-badge i {

    color: #4f46e5 !important;

}


/* =========================================================
   ERROR
========================================================= */

.error-message {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 20px;

    padding: 14px 18px;

    border: 1px solid rgba(239, 68, 68, 0.35);

    border-radius: 10px;

    background: rgba(239, 68, 68, 0.08);

    color: #b91c1c !important;

}


.error-message span {

    color: #b91c1c !important;

}


/* =========================================================
   ACCOUNT CARD
========================================================= */

.account-card {

    margin-bottom: 35px;

    padding: 25px;

    border: 1px solid #e5e7eb;

    border-radius: 18px;

    background: #ffffff;

    box-shadow:
        0 4px 18px
        rgba(0, 0, 0, 0.04);

}


/* =========================================================
   SECTION TITLE
========================================================= */

.section-title {

    display: flex;

    align-items: center;

    gap: 14px;

    margin-bottom: 25px;

}


.title-icon {

    width: 45px;

    height: 45px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #eef2ff;

    color: #4f46e5 !important;

    font-size: 18px;

}


.section-title h2 {

    margin: 0;

    color: #111827 !important;

    font-size: 19px;

    font-weight: 700;

}


.section-title p {

    margin: 4px 0 0;

    color: #6b7280 !important;

    font-size: 12px;

}


/* =========================================================
   ACCOUNT CONTENT
========================================================= */

.account-content {

    display: flex;

    align-items: center;

    gap: 20px;

}


/* =========================================================
   AVATAR
========================================================= */

.profile-avatar {

    width: 70px;

    height: 70px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background:
        linear-gradient(
            135deg,
            #4f46e5,
            #06b6d4
        );

    box-shadow:
        0 8px 25px
        rgba(79, 70, 229, 0.35);

}


.profile-avatar span {

    color: #ffffff !important;

    font-size: 28px;

    font-weight: 900;

}


/* =========================================================
   ACCOUNT INFO
========================================================= */

.account-info {

    flex: 1;

    min-width: 0;

}


.account-info h3 {

    margin: 0 0 8px;

    color: #111827 !important;

    font-size: 20px;

    font-weight: 700;

}


.account-info p {

    display: flex;

    align-items: center;

    gap: 7px;

    margin: 6px 0;

    color: #4b5563 !important;

    font-size: 13px;

}


.account-info p i {

    width: 20px;

    color: #4f46e5 !important;

}


.account-info p span {

    color: #4b5563 !important;

}


.account-info strong {

    color: #4f46e5 !important;

    text-transform: capitalize;

}


/* =========================================================
   EDIT PROFILE
========================================================= */

.edit-profile-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 11px 16px;

    border-radius: 10px;

    background: #4f46e5;

    color: #ffffff !important;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    transition:
        transform 0.2s ease,
        background 0.2s ease;

}


.edit-profile-btn:hover {

    background: #4338ca;

    transform: translateY(-2px);

}


.edit-profile-btn i {

    color: #ffffff !important;

}


/* =========================================================
   QUICK SECTION
========================================================= */

.quick-section {

    margin-bottom: 35px;

}


.section-heading {

    display: flex;

    align-items: center;

    gap: 20px;

    margin-bottom: 20px;

}


.section-label {

    display: block;

    color: #4f46e5 !important;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: 2px;

}


.section-heading h2 {

    margin: 4px 0 0;

    color: #111827 !important;

    font-size: 22px;

    font-weight: 700;

}


.heading-line {

    flex: 1;

    height: 1px;

    background: #e5e7eb;

}


/* =========================================================
   QUICK GRID
========================================================= */

.quick-grid {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 15px;

}


/* =========================================================
   QUICK CARD
========================================================= */

.quick-card {

    position: relative;

    display: flex;

    align-items: center;

    gap: 15px;

    min-height: 100px;

    padding: 20px;

    overflow: hidden;

    box-sizing: border-box;

    border: 1px solid #e5e7eb;

    border-radius: 15px;

    background: #ffffff;

    text-decoration: none;

    transition:
        transform 0.25s ease,
        border-color 0.25s ease,
        box-shadow 0.25s ease;

}


.quick-card:hover {

    transform: translateY(-4px);

    border-color: rgba(99, 102, 241, 0.55);

    box-shadow:
        0 12px 30px
        rgba(0, 0, 0, 0.08);

}


/* =========================================================
   QUICK ICON
========================================================= */

.quick-icon {

    width: 48px;

    height: 48px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #eef2ff;

    color: #4f46e5 !important;

    font-size: 18px;

}


.quick-icon i {

    color: #4f46e5 !important;

}


/* =========================================================
   QUICK TEXT
========================================================= */

.quick-text {

    flex: 1;

    min-width: 0;

}


.quick-text h3 {

    margin: 0 0 5px;

    color: #111827 !important;

    font-size: 15px;

    font-weight: 700;

}


.quick-text p {

    margin: 0;

    color: #6b7280 !important;

    font-size: 11px;

    line-height: 1.5;

}


/* =========================================================
   ARROW
========================================================= */

.arrow {

    flex-shrink: 0;

    color: #9ca3af !important;

    font-size: 12px;

    transition:
        color 0.2s ease,
        transform 0.2s ease;

}


.quick-card:hover .arrow {

    color: #4f46e5 !important;

    transform: translateX(3px);

}


/* =========================================================
   STATUS
========================================================= */

.status-card {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 20px 22px;

    border: 1px solid rgba(34, 197, 94, 0.25);

    border-radius: 15px;

    background: rgba(34, 197, 94, 0.05);

}


.status-left {

    display: flex;

    align-items: center;

    gap: 15px;

}


.status-icon {

    width: 45px;

    height: 45px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: rgba(34, 197, 94, 0.15);

    color: #16a34a !important;

}


.status-icon.inactive {

    background: rgba(239, 68, 68, 0.15);

    color: #dc2626 !important;

}


.status-label {

    display: block;

    margin-bottom: 3px;

    color: #6b7280 !important;

    font-size: 9px;

    font-weight: 800;

    letter-spacing: 2px;

}


.status-card h3 {

    margin: 0;

    color: #111827 !important;

    font-size: 15px;

}


.status-card p {

    margin: 4px 0 0;

    color: #6b7280 !important;

    font-size: 11px;

}


.status-dot {

    width: 10px;

    height: 10px;

    flex-shrink: 0;

    border-radius: 50%;

    background: #22c55e;

    box-shadow:
        0 0 12px
        rgba(34, 197, 94, 0.7);

}


.status-dot.inactive {

    background: #ef4444;

    box-shadow:
        0 0 12px
        rgba(239, 68, 68, 0.7);

}


/* =========================================================
   DARK MODE
========================================================= */

:global(html.dark) .admin-dashboard {

    background: #0b0f17 !important;

    color: #e5e7eb !important;

}


/* HEADER */

:global(html.dark) .dashboard-label {

    color: #818cf8 !important;

}


:global(html.dark) .dashboard-header h1 {

    color: #f9fafb !important;

}


:global(html.dark) .dashboard-header p {

    color: #cbd5e1 !important;

}


:global(html.dark) .admin-badge {

    border-color: rgba(99, 102, 241, 0.5);

    background: rgba(79, 70, 229, 0.15);

    color: #c7d2fe !important;

}


:global(html.dark) .admin-badge i {

    color: #818cf8 !important;

}


/* ERROR */

:global(html.dark) .error-message {

    background: rgba(239, 68, 68, 0.12);

    border-color: rgba(239, 68, 68, 0.4);

    color: #fca5a5 !important;

}


:global(html.dark) .error-message span {

    color: #fca5a5 !important;

}


/* ACCOUNT CARD */

:global(html.dark) .account-card {

    background: #111827 !important;

    border-color: #1f2937 !important;

    box-shadow: none !important;

}


:global(html.dark) .title-icon {

    background: rgba(79, 70, 229, 0.18);

    color: #a5b4fc !important;

}


:global(html.dark) .section-title h2 {

    color: #ffffff !important;

}


:global(html.dark) .section-title p {

    color: #94a3b8 !important;

}


:global(html.dark) .account-info h3 {

    color: #ffffff !important;

}


:global(html.dark) .account-info p {

    color: #cbd5e1 !important;

}


:global(html.dark) .account-info p i {

    color: #06b6d4 !important;

}


:global(html.dark) .account-info p span {

    color: #cbd5e1 !important;

}


:global(html.dark) .account-info strong {

    color: #a5b4fc !important;

}


/* EDIT PROFILE */

:global(html.dark) .edit-profile-btn {

    background: #4f46e5 !important;

    color: #ffffff !important;

}


:global(html.dark) .edit-profile-btn:hover {

    background: #4338ca !important;

}


/* SECTION HEADING */

:global(html.dark) .section-label {

    color: #06b6d4 !important;

}


:global(html.dark) .section-heading h2 {

    color: #ffffff !important;

}


:global(html.dark) .heading-line {

    background: rgba(255, 255, 255, 0.10) !important;

}


/* QUICK CARDS */

:global(html.dark) .quick-card {

    background: rgba(15, 23, 42, 0.72) !important;

    border-color: rgba(255, 255, 255, 0.09) !important;

}


:global(html.dark) .quick-card:hover {

    background: rgba(30, 41, 59, 0.9) !important;

    border-color: rgba(99, 102, 241, 0.6) !important;

    box-shadow:
        0 12px 30px
        rgba(0, 0, 0, 0.2) !important;

}


:global(html.dark) .quick-icon {

    background: rgba(79, 70, 229, 0.18);

    color: #a5b4fc !important;

}


:global(html.dark) .quick-icon i {

    color: #a5b4fc !important;

}


:global(html.dark) .quick-text h3 {

    color: #ffffff !important;

}


:global(html.dark) .quick-text p {

    color: #94a3b8 !important;

}


:global(html.dark) .arrow {

    color: #64748b !important;

}


:global(html.dark) .quick-card:hover .arrow {

    color: #06b6d4 !important;

}


/* STATUS */

:global(html.dark) .status-card {

    border-color: rgba(34, 197, 94, 0.20);

    background: rgba(34, 197, 94, 0.06);

}


:global(html.dark) .status-icon {

    background: rgba(34, 197, 94, 0.15);

    color: #4ade80 !important;

}


:global(html.dark) .status-icon.inactive {

    background: rgba(239, 68, 68, 0.15);

    color: #f87171 !important;

}


:global(html.dark) .status-label {

    color: #94a3b8 !important;

}


:global(html.dark) .status-card h3 {

    color: #ffffff !important;

}


:global(html.dark) .status-card p {

    color: #94a3b8 !important;

}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1000px) {

    .quick-grid {

        grid-template-columns: repeat(2, 1fr);

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .admin-dashboard {

        padding: 20px 15px;

    }


    .dashboard-header {

        flex-direction: column;

        align-items: flex-start;

    }


    .dashboard-header h1 {

        font-size: 27px;

    }


    .admin-badge {

        align-self: flex-start;

    }


    .account-content {

        flex-wrap: wrap;

    }


    .account-info {

        width: calc(100% - 90px);

        flex: none;

    }


    .edit-profile-btn {

        width: 100%;

    }


    .quick-grid {

        grid-template-columns: 1fr;

    }


    .heading-line {

        display: none;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 450px) {

    .account-card {

        padding: 18px;

    }


    .profile-avatar {

        width: 58px;

        height: 58px;

    }


    .profile-avatar span {

        font-size: 22px;

    }


    .account-info h3 {

        font-size: 17px;

    }


    .status-card {

        padding: 16px;

    }
}

</style>