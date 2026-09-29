import { reactive } from 'vue';

import {
    getUser,
    logout as logoutApi
} from '../services/auth';

import { saveUser } from '../services/api';

/*
|--------------------------------------------------------------------------
| AUTH STATE
|--------------------------------------------------------------------------
*/

export const authState = reactive({
    user: null,
    loading: false,
    initialized: false,
});


/*
|--------------------------------------------------------------------------
| LOAD CURRENT USER
|--------------------------------------------------------------------------
*/

export async function loadUser() {
    if (authState.loading) {
        return authState.user;
    }

    authState.loading = true;

    try {
        const response = await getUser();

        console.log('Current user response:', response.data);

        if (
            response.data &&
            response.data.success === false
        ) {
            authState.user = null;
            return null;
        }

        authState.user = response.data?.user ?? null;

        if (authState.user) {
            saveUser(authState.user);
        }

        return authState.user;

    } catch (error) {
        if (error.response?.status === 401) {
            console.warn('User is not authenticated.');
        } else {
            console.error(
                'Load user error:',
                error.response?.data || error.message
            );
        }

        authState.user = null;

        return null;

    } finally {
        authState.loading = false;
        authState.initialized = true;
    }
}


/*
|--------------------------------------------------------------------------
| SET USER
|--------------------------------------------------------------------------
*/

export function setAuthUser(user) {
    authState.user = user ?? null;
    authState.initialized = true;

    if (user) {
        saveUser(user);
    }
}


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

export async function logout() {
    try {
        await logoutApi();
    } catch (error) {
        console.error(
            'Logout error:',
            error.response?.data || error.message
        );
    } finally {
        authState.user = null;
        authState.initialized = true;
        authState.loading = false;

        localStorage.removeItem('auth_user');
    }
}


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

export function isLoggedIn() {
    return !!authState.user;
}

export function isAdmin() {
    return authState.user?.role === 'admin';
}

export function getAuthUser() {
    return authState.user;
}

export function clearAuthState() {
    authState.user = null;
    authState.initialized = true;
    authState.loading = false;
    localStorage.removeItem('auth_user');
}