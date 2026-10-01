// resources/js/router.js
import {
    createRouter,
    createWebHistory
} from 'vue-router';

// =====================================================
// BATCH 1 — PUBLIC / AUTH
// =====================================================

import Home from './pages/Home.vue';
import Login from './pages/auth/login.vue';
import Register from './pages/auth/register.vue';
import RegisterOtp from './pages/auth/register-otp.vue';
import ForgotPassword from './pages/auth/forgot-password.vue';
import PasswordOtp from './pages/auth/password-otp.vue';
import ResetPassword from './pages/auth/reset-password.vue';

// =====================================================
// BATCH 2 — USER LAYOUT + PAGES
// =====================================================

import UserLayout from './layouts/UserLayout.vue';
import UserDashboard from './pages/user/dashboard.vue';
import Marketplace from './pages/user/marketplace.vue';
import Products from './pages/user/products.vue';
import UserProfile from './pages/user/profile.vue';
import UserTheme from './pages/user/theme.vue';
import UserChangePassword from './pages/user/change-password.vue';

// =====================================================
// BATCH 3 — ADMIN LAYOUT + PAGES
// =====================================================

import AdminLayout from './layouts/AdminLayout.vue';
import AdminDashboard from './pages/admin/dashboard.vue';
import AdminUsers from './pages/admin/users/index.vue';
import AdminChats from './pages/admin/chats.vue';
import AdminRatings from './pages/admin/ratings.vue';
import AdminBags from './pages/admin/bags/index.vue';
import AdminBagCreate from './pages/admin/bags/create.vue';
import AdminBagShow from './pages/admin/bags/show.vue';
import AdminBagEdit from './pages/admin/bags/edit.vue';
import AdminProfile from './pages/admin/profile.vue';
import AdminTheme from './pages/admin/theme.vue';
import AdminChangePassword from './pages/admin/change-password.vue';

// =====================================================
// BATCH 4 — USER GROUPS
// =====================================================

import UserGroups from './pages/user/groups/index.vue';
import UserGroupView from './pages/user/groups/show.vue';

// =====================================================
// STORE
// =====================================================

import { authState, loadUser } from './stores/auth';

// =====================================================
// ROUTES
// =====================================================

const routes = [

    /*
    |----------------------------------------------------------------------
    | BATCH 1 — PUBLIC + AUTH
    |----------------------------------------------------------------------
    */

    {
        path: '/',
        name: 'home',
        component: Home,
    },

    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guestOnly: true },
    },

    {
        path: '/register',
        name: 'register',
        component: Register,
        meta: { guestOnly: true },
    },

    {
        path: '/register-otp',
        name: 'register.otp',
        component: RegisterOtp,
    },

    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
    },

    {
        path: '/password-otp',
        name: 'password.otp',
        component: PasswordOtp,
    },

    {
        path: '/reset-password',
        name: 'reset-password',
        component: ResetPassword,
    },

    /*
    |----------------------------------------------------------------------
    | BATCH 2 — USER DASHBOARD
    |----------------------------------------------------------------------
    */

    {
        path: '/dashboard',
        component: UserLayout,
        meta: { requiresAuth: true },

        children: [

            {
                path: '',
                name: 'user.dashboard',
                component: UserDashboard,
            },

            {
                path: 'marketplace',
                name: 'user.marketplace',
                component: Marketplace,
            },

            {
                path: 'products',
                name: 'user.products',
                component: Products,
            },

            {
                path: 'profile',
                name: 'user.profile',
                component: UserProfile,
            },

            {
                path: 'theme',
                name: 'user.theme',
                component: UserTheme,
            },

            {
                path: 'password',
                name: 'user.change-password',
                component: UserChangePassword,
            },

            // ⭐ GROUP CHAT
            {
                path: 'groups',
                name: 'user.groups',
                component: UserGroups,
            },

            {
                path: 'groups/:id',
                name: 'user.group',
                component: UserGroupView,
            },

        ],
    },

    /*
    |----------------------------------------------------------------------
    | BATCH 3 — ADMIN
    |----------------------------------------------------------------------
    */

    {
        path: '/admin',
        component: AdminLayout,
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },

        children: [

            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: AdminDashboard,
            },

            {
                path: 'users',
                name: 'admin.users',
                component: AdminUsers,
            },

            {
                path: 'chats',
                name: 'admin.chats',
                component: AdminChats,
            },

            {
                path: 'ratings',
                name: 'admin.ratings',
                component: AdminRatings,
            },

            {
                path: 'bags',
                name: 'admin.bags',
                component: AdminBags,
            },

            {
                path: 'bags/create',
                name: 'admin.bags.create',
                component: AdminBagCreate,
            },

            {
                path: 'bags/:id',
                name: 'admin.bags.show',
                component: AdminBagShow,
            },

            {
                path: 'bags/:id/edit',
                name: 'admin.bags.edit',
                component: AdminBagEdit,
            },

            {
                path: 'profile',
                name: 'admin.profile',
                component: AdminProfile,
            },

            {
                path: 'theme',
                name: 'admin.theme',
                component: AdminTheme,
            },

            {
                path: 'change-password',
                name: 'admin.change-password',
                component: AdminChangePassword,
            },

        ],
    },

    /*
    |----------------------------------------------------------------------
    | BATCH 5 — PUBLIC BAG DETAILS
    |----------------------------------------------------------------------
    */

    {
        path: '/bags/:id',
        name: 'bag.show',
        component: AdminBagShow,
    },

    /*
    |----------------------------------------------------------------------
    | 404
    |----------------------------------------------------------------------
    */

    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },

];

// =====================================================
// ROUTER
// =====================================================

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

// =====================================================
// GLOBAL GUARD — BACKEND VERIFIED
// =====================================================

router.beforeEach(async (to, from, next) => {

    /*
    |----------------------------------------------------------------------
    | 1. LOAD USER — SIRF JAB TAK INITIALIZE NA HO
    |----------------------------------------------------------------------
    */

    if (!authState.initialized) {
        try {
            await loadUser();
        } catch (err) {
            console.warn('Router guard: loadUser failed', err);
        }
    }

    /*
    |----------------------------------------------------------------------
    | 2. CURRENT STATE
    |----------------------------------------------------------------------
    */

    const isAuth = !!authState.user;
    const user = authState.user;

    /*
    |----------------------------------------------------------------------
    | 3. GUEST-ONLY ROUTES
    |----------------------------------------------------------------------
    */

    if (to.meta.guestOnly && isAuth) {
        return next(
            user.role === 'admin'
                ? { name: 'admin.dashboard' }
                : { name: 'user.dashboard' }
        );
    }

    /*
    |----------------------------------------------------------------------
    | 4. PROTECTED ROUTES
    |----------------------------------------------------------------------
    */

    if (to.meta.requiresAuth && !isAuth) {
        return next({
            name: 'login',
            query: { redirect: to.fullPath },
        });
    }

    /*
    |----------------------------------------------------------------------
    | 5. ADMIN ONLY
    |----------------------------------------------------------------------
    */

    if (to.meta.requiresAdmin && isAuth && user.role !== 'admin') {
        return next({ name: 'user.dashboard' });
    }

    /*
    |----------------------------------------------------------------------
    | 6. ALLOW
    |----------------------------------------------------------------------
    */

    next();
});

// =====================================================
// EXPORT
// =====================================================

export default router;