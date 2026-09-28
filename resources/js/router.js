import {
    createRouter,
    createWebHistory
} from 'vue-router';


// =====================================================
// PUBLIC / AUTH PAGES
// =====================================================

import Home from './pages/Home.vue';

import Login from './pages/auth/login.vue';
import Register from './pages/auth/register.vue';
import RegisterOtp from './pages/auth/register-otp.vue';
import ForgotPassword from './pages/auth/forgot-password.vue';
import PasswordOtp from './pages/auth/password-otp.vue';
import ResetPassword from './pages/auth/reset-password.vue';


// =====================================================
// USER LAYOUT
// =====================================================

import UserLayout from './layouts/UserLayout.vue';

import UserDashboard from './pages/user/dashboard.vue';
import Marketplace from './pages/user/marketplace.vue';
import Products from './pages/user/products.vue';
import UserProfile from './pages/user/profile.vue';
import UserTheme from './pages/user/theme.vue';
import UserChangePassword from './pages/user/change-password.vue';


// =====================================================
// ADMIN LAYOUT
// =====================================================

import AdminLayout from './layouts/AdminLayout.vue';

import AdminDashboard from './pages/admin/dashboard.vue';
import AdminUsers from './pages/admin/users/index.vue';

import AdminChats from './pages/admin/chats.vue';

import AdminBags from './pages/admin/bags/index.vue';
import AdminBagCreate from './pages/admin/bags/create.vue';
import AdminBagShow from './pages/admin/bags/show.vue';
import AdminBagEdit from './pages/admin/bags/edit.vue';

import AdminProfile from './pages/admin/profile.vue';
import AdminTheme from './pages/admin/theme.vue';
import AdminChangePassword from './pages/admin/change-password.vue';


// =====================================================
// ROUTES
// =====================================================

const routes = [

    // =================================================
    // PUBLIC
    // =================================================

    {
        path: '/',
        name: 'home',
        component: Home
    },


    // =================================================
    // AUTH
    // =================================================

    {
        path: '/login',
        name: 'login',
        component: Login
    },

    {
        path: '/register',
        name: 'register',
        component: Register
    },

    {
        path: '/register-otp',
        name: 'register.otp',
        component: RegisterOtp
    },

    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword
    },

    {
        path: '/password-otp',
        name: 'password.otp',
        component: PasswordOtp
    },

    {
        path: '/reset-password',
        name: 'reset-password',
        component: ResetPassword
    },


    // =================================================
    // USER DASHBOARD
    // =================================================

    {
        path: '/dashboard',
        component: UserLayout,

        children: [

            {
                path: '',
                name: 'user.dashboard',
                component: UserDashboard
            },

            {
                path: 'marketplace',
                name: 'user.marketplace',
                component: Marketplace
            },

            {
                path: 'products',
                name: 'user.products',
                component: Products
            },

            {
                path: 'profile',
                name: 'user.profile',
                component: UserProfile
            },

            {
                path: 'theme',
                name: 'user.theme',
                component: UserTheme
            },

            {
                path: 'password',
                name: 'user.change-password',
                component: UserChangePassword
            }

        ]
    },


    // =================================================
    // ADMIN
    // =================================================

    {
        path: '/admin',
        component: AdminLayout,

        children: [

            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: AdminDashboard
            },

            // -------------------------------
            // USERS
            // -------------------------------

            {
                path: 'users',
                name: 'admin.users',
                component: AdminUsers
            },

            // -------------------------------
            // CUSTOMER CHAT
            // -------------------------------

            {
                path: 'chats',
                name: 'admin.chats',
                component: AdminChats
            },

            // -------------------------------
            // BAGS
            // -------------------------------

            {
                path: 'bags',
                name: 'admin.bags',
                component: AdminBags
            },

            {
                path: 'bags/create',
                name: 'admin.bags.create',
                component: AdminBagCreate
            },

            {
                path: 'bags/:id',
                name: 'admin.bags.show',
                component: AdminBagShow
            },

            {
                path: 'bags/:id/edit',
                name: 'admin.bags.edit',
                component: AdminBagEdit
            },

            // -------------------------------
            // ADMIN PROFILE
            // -------------------------------

            {
                path: 'profile',
                name: 'admin.profile',
                component: AdminProfile
            },

            // -------------------------------
            // ADMIN THEME
            // -------------------------------

            {
                path: 'theme',
                name: 'admin.theme',
                component: AdminTheme
            },

            // -------------------------------
            // ADMIN PASSWORD
            // -------------------------------

            {
                path: 'change-password',
                name: 'admin.change-password',
                component: AdminChangePassword
            }

        ]
    },


    // =================================================
    // 404
    // =================================================

    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }

];


// =====================================================
// CREATE ROUTER
// =====================================================

const router = createRouter({

    history: createWebHistory(),

    routes,

    scrollBehavior() {
        return {
            top: 0
        };
    }

});


// =====================================================
// EXPORT
// =====================================================

export default router;