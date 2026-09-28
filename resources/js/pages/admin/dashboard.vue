<template>
    <div class="user-dashboard">

        <!-- =====================================================
             WELCOME
        ====================================================== -->

        <section class="welcome-box">

            <div class="welcome-content">

                <div class="welcome-text">

                    <span class="welcome-small">
                        USER DASHBOARD
                    </span>

                    <h2>
                        Welcome back, {{ userName }}! 👋
                    </h2>

                    <p>
                        Manage your account, view products and
                        update your profile from your dashboard.
                    </p>

                </div>

                <div class="welcome-icon">
                    <i class="fa-solid fa-house"></i>
                </div>

            </div>

        </section>


        <!-- =====================================================
             ERROR
        ====================================================== -->

        <div
            v-if="error"
            class="error-box"
        >
            <i class="fa-solid fa-circle-exclamation"></i>

            {{ error }}
        </div>


        <!-- =====================================================
             USER INFORMATION
        ====================================================== -->

        <section class="section-card">

            <div class="section-header">

                <div>

                    <h4>
                        <i class="fa-solid fa-user me-2"></i>
                        My Account
                    </h4>

                    <p>
                        Your account information.
                    </p>

                </div>

                <!-- ADMIN PROFILE -->

                <router-link
                    :to="{ name: 'admin.profile' }"
                    class="edit-profile-btn"
                >
                    <i class="fa-solid fa-pen"></i>
                    Edit Profile
                </router-link>

            </div>


            <div class="user-info-grid">

                <!-- NAME -->

                <div class="info-card">

                    <div class="info-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div class="info-content">

                        <span>Name</span>

                        <strong>
                            {{ userName }}
                        </strong>

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="info-card">

                    <div class="info-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </div>

                    <div class="info-content">

                        <span>Email</span>

                        <strong>
                            {{ userEmail }}
                        </strong>

                    </div>

                </div>


                <!-- ROLE -->

                <div class="info-card">

                    <div class="info-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div class="info-content">

                        <span>Account Type</span>

                        <strong>
                            {{ userRole }}
                        </strong>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="info-card">

                    <div class="info-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="info-content">

                        <span>Account Status</span>

                        <strong
                            :class="
                                isActive
                                    ? 'status-active'
                                    : 'status-inactive'
                            "
                        >

                            <span class="status-dot"></span>

                            {{ isActive ? 'Active' : 'Inactive' }}

                        </strong>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             QUICK ACTIONS
        ====================================================== -->

        <section class="section-card">

            <div class="section-header">

                <div>

                    <h4>
                        <i class="fa-solid fa-bolt me-2"></i>
                        Quick Actions
                    </h4>

                    <p>
                        Quickly access your account features.
                    </p>

                </div>

            </div>


            <div class="quick-grid">

                <!-- =================================================
                     PRODUCTS
                     /admin/bags
                ================================================== -->

                <router-link
                    :to="{ name: 'admin.bags' }"
                    class="quick-btn"
                >

                    <span class="quick-icon">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </span>

                    <span class="quick-text">

                        <strong>
                            Products
                        </strong>

                        <small>
                            View and manage products
                        </small>

                    </span>

                    <i
                        class="fa-solid fa-arrow-right arrow-icon"
                    ></i>

                </router-link>


                <!-- =================================================
                     MY PROFILE
                     /admin/profile
                ================================================== -->

                <router-link
                    :to="{ name: 'admin.profile' }"
                    class="quick-btn"
                >

                    <span class="quick-icon">
                        <i class="fa-solid fa-user"></i>
                    </span>

                    <span class="quick-text">

                        <strong>
                            My Profile
                        </strong>

                        <small>
                            Manage your profile
                        </small>

                    </span>

                    <i
                        class="fa-solid fa-arrow-right arrow-icon"
                    ></i>

                </router-link>


                <!-- =================================================
                     USER MANAGEMENT
                     /admin/users
                ================================================== -->

                <router-link
                    :to="{ name: 'admin.users' }"
                    class="quick-btn"
                >

                    <span class="quick-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span class="quick-text">

                        <strong>
                            User Management
                        </strong>

                        <small>
                            Manage registered users
                        </small>

                    </span>

                    <i
                        class="fa-solid fa-arrow-right arrow-icon"
                    ></i>

                </router-link>

            </div>

        </section>


        <!-- =====================================================
             ACCOUNT STATUS
        ====================================================== -->

        <section class="status-section">

            <div class="status-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="status-content">

                <strong>
                    Account is {{ isActive ? 'Active' : 'Inactive' }}
                </strong>

                <p>
                    {{
                        isActive
                            ? 'Your account is active and ready to use.'
                            : 'Your account is currently inactive.'
                    }}
                </p>

            </div>

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


// =========================================================
// USER
// =========================================================

const user = computed(() => {

    return authState.user || {};

});


// =========================================================
// USER NAME
// =========================================================

const userName = computed(() => {

    return user.value.name || 'User';

});


// =========================================================
// USER EMAIL
// =========================================================

const userEmail = computed(() => {

    return user.value.email || '-';

});


// =========================================================
// USER ROLE
// =========================================================

const userRole = computed(() => {

    const role = user.value.role || 'user';

    return (
        role.charAt(0).toUpperCase() +
        role.slice(1)
    );

});


// =========================================================
// USER STATUS
// =========================================================

const isActive = computed(() => {

    return (
        user.value.is_active !== false &&
        user.value.is_active !== 0
    );

});


// =========================================================
// ERROR
// =========================================================

const error = computed(() => {

    return authState.error || '';

});


// =========================================================
// LOAD USER
// =========================================================

onMounted(async () => {

    try {

        if (!authState.initialized) {

            await loadUser();

        }

    } catch (err) {

        console.error(
            'User dashboard error:',
            err
        );

    }

});

</script>


<style scoped>

/* =========================================================
   DASHBOARD
========================================================= */

.user-dashboard {
    width: 100%;
    padding: 8px 0 30px;
    box-sizing: border-box;
}


/* =========================================================
   WELCOME BOX
========================================================= */

.welcome-box {
    background:
        linear-gradient(
            135deg,
            #111827,
            #1f2937
        );

    border-radius: 18px;
    padding: 30px;
    color: #ffffff;
    margin-bottom: 24px;

    box-shadow:
        0 10px 30px rgba(0, 0, 0, 0.08);
}


.welcome-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}


.welcome-small {
    display: inline-block;
    margin-bottom: 7px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    opacity: .65;
}


.welcome-box h2 {
    margin: 0 0 8px;
    font-size: 27px;
    font-weight: 700;
}


.welcome-box p {
    margin: 0;
    max-width: 650px;
    font-size: 14px;
    line-height: 1.6;
    opacity: .75;
}


.welcome-icon {
    width: 70px;
    height: 70px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 18px;

    background:
        rgba(255, 255, 255, .10);

    font-size: 28px;
}


/* =========================================================
   ERROR
========================================================= */

.error-box {
    padding: 14px 18px;
    margin-bottom: 20px;
    border-radius: 12px;

    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;

    font-size: 14px;
}


.error-box i {
    margin-right: 7px;
}


/* =========================================================
   SECTION CARD
========================================================= */

.section-card {
    background: #ffffff;

    border: 1px solid #e5e7eb;
    border-radius: 16px;

    padding: 24px;
    margin-bottom: 24px;

    box-shadow:
        0 5px 18px rgba(0, 0, 0, .04);

    box-sizing: border-box;
}


/* =========================================================
   SECTION HEADER
========================================================= */

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 15px;
    margin-bottom: 20px;
}


.section-header h4 {
    margin: 0;

    color: #111827;

    font-size: 18px;
    font-weight: 700;
}


.section-header p {
    margin: 6px 0 0;

    color: #9ca3af;

    font-size: 13px;
}


/* =========================================================
   EDIT PROFILE
========================================================= */

.edit-profile-btn {
    display: inline-flex;

    align-items: center;
    gap: 6px;

    padding: 8px 13px;

    border: 1px solid #e5e7eb;
    border-radius: 8px;

    background: #ffffff;
    color: #374151;

    text-decoration: none;

    font-size: 12px;
    font-weight: 600;

    white-space: nowrap;

    transition: all .2s ease;
}


.edit-profile-btn:hover {
    background: #f9fafb;
    color: #111827;
}


/* =========================================================
   USER INFORMATION GRID
========================================================= */

.user-info-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 15px;
}


/* =========================================================
   INFO CARD
========================================================= */

.info-card {
    display: flex;
    align-items: center;

    gap: 14px;

    padding: 17px;

    border: 1px solid #edf0f2;
    border-radius: 12px;

    background: #fafafa;
}


.info-icon {
    width: 44px;
    height: 44px;
    min-width: 44px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    color: #374151;

    font-size: 16px;
}


.info-content {
    min-width: 0;
}


.info-content span {
    display: block;

    margin-bottom: 4px;

    color: #9ca3af;

    font-size: 11px;
}


.info-content strong {
    display: block;

    color: #111827;

    font-size: 14px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.status-active {
    display: flex !important;

    align-items: center;

    gap: 7px;

    color: #15803d !important;
}


.status-inactive {
    display: flex !important;

    align-items: center;

    gap: 7px;

    color: #dc2626 !important;
}


.status-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: currentColor;

    flex-shrink: 0;
}


/* =========================================================
   QUICK GRID
========================================================= */

.quick-grid {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 12px;
}


/* =========================================================
   QUICK BUTTON
========================================================= */

.quick-btn {
    min-height: 90px;

    display: flex;
    align-items: center;

    gap: 12px;

    padding: 16px;

    border: 1px solid #e5e7eb;

    border-radius: 13px;

    background: #ffffff;

    color: #111827;

    text-decoration: none;

    transition: all .25s ease;

    box-sizing: border-box;
}


.quick-btn:hover {
    color: #111827;

    border-color: #d1d5db;

    transform: translateY(-3px);

    box-shadow:
        0 8px 20px rgba(0, 0, 0, .07);
}


/* =========================================================
   QUICK ICON
========================================================= */

.quick-icon {
    width: 42px;
    height: 42px;
    min-width: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: #f3f4f6;

    font-size: 17px;
}


/* =========================================================
   QUICK TEXT
========================================================= */

.quick-text {
    min-width: 0;
}


.quick-text strong {
    display: block;

    font-size: 13px;

    font-weight: 700;
}


.quick-text small {
    display: block;

    margin-top: 3px;

    color: #9ca3af;

    font-size: 11px;
}


/* =========================================================
   ARROW
========================================================= */

.arrow-icon {
    margin-left: auto;

    color: #9ca3af;

    font-size: 12px;

    transition:
        transform .2s ease;
}


.quick-btn:hover .arrow-icon {
    transform: translateX(4px);
}


/* =========================================================
   ACCOUNT STATUS
========================================================= */

.status-section {
    display: flex;

    align-items: center;

    gap: 15px;

    padding: 20px;

    border: 1px solid #e5e7eb;

    border-radius: 15px;

    background: #ffffff;

    box-shadow:
        0 5px 18px rgba(0, 0, 0, .04);
}


.status-icon {
    width: 48px;
    height: 48px;
    min-width: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #f3f4f6;

    color: #15803d;

    font-size: 20px;
}


.status-content strong {
    display: block;

    color: #111827;

    font-size: 14px;
}


.status-content p {
    margin: 4px 0 0;

    color: #9ca3af;

    font-size: 12px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 900px) {

    .user-info-grid {
        grid-template-columns: 1fr;
    }

    .quick-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 650px) {

    .welcome-box {
        padding: 22px;
        border-radius: 15px;
    }


    .welcome-icon {
        display: none;
    }


    .welcome-box h2 {
        font-size: 22px;
    }


    .welcome-box p {
        font-size: 13px;
    }


    .section-card {
        padding: 18px;
    }


    .section-header {
        align-items: flex-start;
        flex-direction: column;
    }


    .edit-profile-btn {
        width: 100%;
        justify-content: center;
    }


    .quick-grid {
        grid-template-columns: 1fr;
    }


    .status-section {
        padding: 16px;
    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 480px) {

    .user-dashboard {
        padding-bottom: 20px;
    }


    .welcome-box {
        padding: 18px;
    }


    .welcome-box h2 {
        font-size: 20px;
    }


    .section-card {
        padding: 15px;
    }


    .info-card {
        padding: 14px;
    }

}

</style>