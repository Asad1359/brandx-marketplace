<template>
    <div class="dashboard">

        <!-- WELCOME -->
        <section class="welcome-card">
            <div>
                <span class="badge">USER DASHBOARD</span>

                <h2>
                    Welcome back, {{ userName }}!
                </h2>

                <p>
                    Manage your account, profile, marketplace
                    and settings from your dashboard.
                </p>
            </div>

            <div class="welcome-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </section>

        <!-- ACCOUNT -->
        <section class="account-card">

            <div class="section-header">

                <div>
                    <h3>My Account</h3>
                    <p>Your account information</p>
                </div>

                <router-link
                    :to="{ name: 'user.profile' }"
                    class="edit-btn"
                >
                    <i class="fa-solid fa-pen"></i>
                    Edit Profile
                </router-link>

            </div>

            <div class="account-grid">

                <div class="info-box">
                    <span>Name</span>
                    <strong>{{ userName }}</strong>
                </div>

                <div class="info-box">
                    <span>Email</span>
                    <strong>{{ userEmail }}</strong>
                </div>

                <div class="info-box">
                    <span>Account Type</span>
                    <strong>{{ userRole }}</strong>
                </div>

                <div class="info-box">

                    <span>Status</span>

                    <strong
                        :class="
                            isActive
                                ? 'active'
                                : 'inactive'
                        "
                    >
                        {{ isActive ? 'Active' : 'Inactive' }}
                    </strong>

                </div>

            </div>

        </section>

        <!-- QUICK ACTIONS -->
        <section class="actions-card">

            <div class="section-title">

                <h3>Quick Actions</h3>

                <p>
                    Manage your account and marketplace
                </p>

            </div>

            <div class="actions-grid">

                <!-- MARKETPLACE -->
                <router-link
                    :to="{ name: 'user.marketplace' }"
                    class="action-card marketplace-card"
                >

                    <div class="action-icon">
                        <i class="fa-solid fa-store"></i>
                    </div>

                    <div>
                        <h4>Marketplace</h4>

                        <p>
                            Search products from marketplaces
                        </p>
                    </div>

                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </router-link>

                <!-- PROFILE -->
                <router-link
                    :to="{ name: 'user.profile' }"
                    class="action-card"
                >

                    <div class="action-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h4>Profile</h4>

                        <p>
                            Update your profile information
                        </p>
                    </div>

                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </router-link>

                <!-- THEME -->
                <router-link
                    :to="{ name: 'user.theme' }"
                    class="action-card"
                >

                    <div class="action-icon">
                        <i class="fa-solid fa-palette"></i>
                    </div>

                    <div>
                        <h4>Theme</h4>

                        <p>
                            Customize dashboard appearance
                        </p>
                    </div>

                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </router-link>

                <!-- PASSWORD -->
                <router-link
                    :to="{ name: 'user.change-password' }"
                    class="action-card"
                >

                    <div class="action-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <div>
                        <h4>Password</h4>

                        <p>
                            Change your account password
                        </p>
                    </div>

                    <i
                        class="fa-solid fa-chevron-right arrow"
                    ></i>

                </router-link>

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

const user = computed(() => {
    return authState.user || {};
});

const userName = computed(() => {
    return user.value.name || 'User';
});

const userEmail = computed(() => {
    return user.value.email || '-';
});

const userRole = computed(() => {

    const role =
        user.value.role || 'user';

    return (
        role.charAt(0).toUpperCase() +
        role.slice(1)
    );
});

const isActive = computed(() => {

    return (
        user.value.is_active !== false &&
        user.value.is_active !== 0
    );
});

onMounted(async () => {

    try {

        if (!authState.initialized) {
            await loadUser();
        }

    } catch (error) {

        console.error(
            'User dashboard error:',
            error
        );
    }
});
</script>

<style scoped>

.dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

/* =========================================================
   WELCOME
========================================================= */

.welcome-card {
    background:
        linear-gradient(
            135deg,
            #111827,
            #2563eb
        );

    color: white;

    border-radius: 16px;

    padding: 30px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 25px;
}

.badge {
    display: inline-block;

    background:
        rgba(255,255,255,0.15);

    padding: 6px 11px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: .5px;
}

.welcome-card h2 {
    margin: 15px 0 8px;

    font-size: 28px;
}

.welcome-card p {
    margin: 0;

    color: #dbeafe;

    font-size: 14px;
}

.welcome-icon {
    width: 75px;
    height: 75px;

    border-radius: 20px;

    background:
        rgba(255,255,255,.15);

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 30px;
}

/* =========================================================
   CARDS
========================================================= */

.account-card,
.actions-card {
    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 15px;

    padding: 25px;

    margin-bottom: 25px;
}

.section-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;
}

.section-header h3,
.section-title h3 {
    margin: 0;

    color: #111827;

    font-size: 18px;
}

.section-header p,
.section-title p {
    margin: 5px 0 0;

    color: #6b7280;

    font-size: 13px;
}

.edit-btn {
    text-decoration: none;

    background: #eff6ff;

    color: #2563eb;

    padding: 9px 13px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;
}

.edit-btn:hover {
    background: #dbeafe;
}

/* =========================================================
   ACCOUNT GRID
========================================================= */

.account-grid {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;
}

.info-box {
    background: #f9fafb;

    border: 1px solid #e5e7eb;

    border-radius: 10px;

    padding: 18px;
}

.info-box span {
    display: block;

    color: #6b7280;

    font-size: 12px;

    margin-bottom: 7px;
}

.info-box strong {
    color: #111827;

    font-size: 14px;

    word-break: break-word;
}

.info-box strong.active {
    color: #16a34a;
}

.info-box strong.inactive {
    color: #dc2626;
}

/* =========================================================
   ACTIONS
========================================================= */

.section-title {
    margin-bottom: 20px;
}

.actions-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;
}

.action-card {
    text-decoration: none;

    border: 1px solid #e5e7eb;

    border-radius: 12px;

    padding: 18px;

    display: flex;

    align-items: center;

    gap: 13px;

    transition: .2s;

    background: white;
}

.action-card:hover {
    border-color: #2563eb;

    transform:
        translateY(-2px);

    box-shadow:
        0 8px 20px
        rgba(37,99,235,.08);
}

.action-icon {
    width: 45px;
    height: 45px;

    flex-shrink: 0;

    border-radius: 10px;

    background: #eff6ff;

    color: #2563eb;

    display: flex;

    align-items: center;
    justify-content: center;
}

.action-card h4 {
    margin: 0;

    color: #111827;

    font-size: 14px;
}

.action-card p {
    margin: 4px 0 0;

    color: #6b7280;

    font-size: 12px;
}

.arrow {
    margin-left: auto;

    color: #9ca3af;

    font-size: 12px;
}

.marketplace-card {
    border-color: #dbeafe;
}

.marketplace-card .action-icon {
    background: #2563eb;
    color: white;
}

/* =========================================================
   DARK MODE
========================================================= */

:global(html.dark) .dashboard {
    background: #0b0f17 !important;
}

/* WELCOME */

:global(html.dark) .welcome-card {
    background:
        linear-gradient(
            135deg,
            #0f172a,
            #1e3a8a
        ) !important;

    color: #f9fafb !important;
}

:global(html.dark) .badge {
    background: rgba(255, 255, 255, 0.10) !important;
    color: #c7d2fe !important;
}

:global(html.dark) .welcome-card h2 {
    color: #f9fafb !important;
}

:global(html.dark) .welcome-card p {
    color: #cbd5e1 !important;
}

:global(html.dark) .welcome-icon {
    background: rgba(255, 255, 255, 0.10) !important;
    color: #a5b4fc !important;
}

/* ACCOUNT CARD + ACTIONS CARD */

:global(html.dark) .account-card,
:global(html.dark) .actions-card {
    background: #111827 !important;
    border-color: #1f2937 !important;
}

:global(html.dark) .section-header h3,
:global(html.dark) .section-title h3 {
    color: #f9fafb !important;
}

:global(html.dark) .section-header p,
:global(html.dark) .section-title p {
    color: #94a3b8 !important;
}

:global(html.dark) .edit-btn {
    background: rgba(37, 99, 235, 0.18) !important;
    color: #93c5fd !important;
}

:global(html.dark) .edit-btn:hover {
    background: rgba(37, 99, 235, 0.28) !important;
}

/* INFO BOXES */

:global(html.dark) .info-box {
    background: #0f172a !important;
    border-color: #1f2937 !important;
}

:global(html.dark) .info-box span {
    color: #94a3b8 !important;
}

:global(html.dark) .info-box strong {
    color: #f9fafb !important;
}

:global(html.dark) .info-box strong.active {
    color: #4ade80 !important;
}

:global(html.dark) .info-box strong.inactive {
    color: #f87171 !important;
}

/* ACTION CARDS */

:global(html.dark) .action-card {
    background: #0f172a !important;
    border-color: #1f2937 !important;
}

:global(html.dark) .action-card:hover {
    border-color: #2563eb !important;
    box-shadow:
        0 8px 20px
        rgba(37, 99, 235, 0.20) !important;
}

:global(html.dark) .action-icon {
    background: rgba(37, 99, 235, 0.18) !important;
    color: #93c5fd !important;
}

:global(html.dark) .action-card h4 {
    color: #f9fafb !important;
}

:global(html.dark) .action-card p {
    color: #94a3b8 !important;
}

:global(html.dark) .arrow {
    color: #64748b !important;
}

:global(html.dark) .marketplace-card {
    border-color: rgba(37, 99, 235, 0.35) !important;
}

:global(html.dark) .marketplace-card .action-icon {
    background: #2563eb !important;
    color: #ffffff !important;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .account-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .actions-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {

    .welcome-card {
        padding: 22px;
    }

    .welcome-card h2 {
        font-size: 22px;
    }

    .welcome-icon {
        display: none;
    }

    .section-header {
        align-items: flex-start;

        gap: 15px;

        flex-direction: column;
    }

    .account-grid {
        grid-template-columns: 1fr;
    }

    .account-card,
    .actions-card {
        padding: 18px;
    }
}

</style>