import axios from 'axios';

/*
|--------------------------------------------------------------------------
| Laravel SPA API CLIENT (Sanctum)
|--------------------------------------------------------------------------
*/

const api = axios.create({
    baseURL: '/api',

    withCredentials: true,
    withXSRFToken: true,

    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

let csrfFetched = false;

export async function csrf(force = false) {
    if (csrfFetched && !force) {
        return;
    }

    await axios.get('/sanctum/csrf-cookie', {
        withCredentials: true,

        headers: {
            Accept: 'application/json',
        },
    });

    csrfFetched = true;
}


/*
|--------------------------------------------------------------------------
| Request Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.request.use(
    (config) => {
        config.withCredentials = true;
        config.withXSRFToken = true;

        return config;
    },

    (error) => Promise.reject(error)
);


/*
|--------------------------------------------------------------------------
| Response Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.response.use(
    (response) => response,

    (error) => {
        const status = error.response?.status;

        if (status === 419) {
            // CSRF token expired — refetch on next request
            csrfFetched = false;
        }

        console.error(
            'API Error:',
            status || 'No Status',
            error.response?.data || error.message
        );

        return Promise.reject(error);
    }
);


/*
|--------------------------------------------------------------------------
| LOCAL USER HELPERS
|--------------------------------------------------------------------------
*/

export const saveUser = (user) => {
    if (user) {
        localStorage.setItem(
            'auth_user',
            JSON.stringify(user)
        );
    }
};

export const getStoredUser = () => {
    const raw = localStorage.getItem('auth_user');

    if (!raw) {
        return null;
    }

    try {
        return JSON.parse(raw);
    } catch (error) {
        console.error('Invalid stored user:', error);

        localStorage.removeItem('auth_user');

        return null;
    }
};

export const clearAuth = () => {
    localStorage.removeItem('auth_user');
    localStorage.removeItem('registration_email');
    localStorage.removeItem('password_reset_email');
};


/*
|--------------------------------------------------------------------------
| EXPORT DEFAULT
|--------------------------------------------------------------------------
*/

export default api;