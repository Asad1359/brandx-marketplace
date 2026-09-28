import { reactive } from 'vue';

import {
    getUser,
    logout as logoutApi
} from '../services/auth';


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

    /*
    |--------------------------------------------------------------------------
    | Already loading
    |--------------------------------------------------------------------------
    */

    if (authState.loading) {
        return authState.user;
    }

    authState.loading = true;

    try {

        const response = await getUser();

        console.log(
            'Current user response:',
            response.data
        );


        /*
        |--------------------------------------------------------------------------
        | Laravel Response
        |--------------------------------------------------------------------------
        |
        | Expected:
        |
        | {
        |     success: true,
        |     user: {...}
        | }
        |
        */

        if (
            response.data &&
            response.data.success === false
        ) {

            authState.user = null;

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Set Current User
        |--------------------------------------------------------------------------
        */

        authState.user =
            response.data?.user ??
            null;


        return authState.user;

    } catch (error) {

        /*
        |--------------------------------------------------------------------------
        | 401 = Not Authenticated
        |--------------------------------------------------------------------------
        */

        if (error.response?.status === 401) {

            console.warn(
                'User is not authenticated.'
            );

        } else {

            console.error(
                'Load user error:',
                error.response?.data ||
                error.message
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Do NOT call logout API here
        |--------------------------------------------------------------------------
        |
        | A failed /api/user request should not
        | itself trigger logout.
        |
        */

        authState.user = null;

        return null;

    } finally {

        authState.loading = false;

        authState.initialized = true;
    }
}


/*
|--------------------------------------------------------------------------
| LOGIN STATE
|--------------------------------------------------------------------------
|
| Login API ke baad user ko directly state mein set
| karne ke liye.
|
| Is function mein setUser naam ka koi function nahi hai.
|--------------------------------------------------------------------------
*/

export function setAuthUser(user) {

    authState.user = user ?? null;

    authState.initialized = true;
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
            error.response?.data ||
            error.message
        );

    } finally {

        /*
        |--------------------------------------------------------------------------
        | Clear frontend auth state
        |--------------------------------------------------------------------------
        */

        authState.user = null;

        authState.initialized = true;

        authState.loading = false;
    }
}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

export function isLoggedIn() {

    return !!authState.user;
}


/*
|--------------------------------------------------------------------------
| CHECK ADMIN
|--------------------------------------------------------------------------
*/

export function isAdmin() {

    return authState.user?.role === 'admin';
}


/*
|--------------------------------------------------------------------------
| GET AUTH USER
|--------------------------------------------------------------------------
*/

export function getAuthUser() {

    return authState.user;
}


/*
|--------------------------------------------------------------------------
| CLEAR AUTH STATE
|--------------------------------------------------------------------------
*/

export function clearAuthState() {

    authState.user = null;

    authState.initialized = true;

    authState.loading = false;
}