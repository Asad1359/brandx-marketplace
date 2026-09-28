import axios from 'axios';

/*
|--------------------------------------------------------------------------
| Laravel Backend URL
|--------------------------------------------------------------------------
|
| Vue:    http://127.0.0.1:5173
| Laravel: http://127.0.0.1:8000
|
| Vite proxy ke through /api Laravel par jayega.
|
|--------------------------------------------------------------------------
*/

const api = axios.create({
    baseURL: '/api',

    withCredentials: true,

    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

/*
|--------------------------------------------------------------------------
| Get CSRF Cookie
|--------------------------------------------------------------------------
*/

export async function csrf() {
    return await axios.get('/sanctum/csrf-cookie', {
        withCredentials: true,

        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

/*
|--------------------------------------------------------------------------
| Request Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.request.use(
    (config) => {
        config.withCredentials = true;

        return config;
    },

    (error) => {
        return Promise.reject(error);
    }
);

/*
|--------------------------------------------------------------------------
| Response Interceptor
|--------------------------------------------------------------------------
*/

api.interceptors.response.use(
    (response) => {
        return response;
    },

    (error) => {
        console.error(
            'API Error:',
            error.response?.status || 'No Status',
            error.response?.data || error.message
        );

        return Promise.reject(error);
    }
);

/*
|--------------------------------------------------------------------------
| AUTH API
|--------------------------------------------------------------------------
*/

/*
| Register
*/
export const register = async (data) => {
    await csrf();

    return api.post('/register', {
        name: data.name,
        email: data.email,
        password: data.password,
        password_confirmation:
            data.password_confirmation || data.passwordConfirmation,
    });
};

/*
| Verify Registration OTP
*/
export const verifyRegistrationOtp = async (otp) => {
    return api.post('/register/verify-otp', {
        otp: otp,
    });
};

/*
| Resend Registration OTP
*/
export const resendRegistrationOtp = async (email = null) => {
    const data = {};

    if (email) {
        data.email = email;
    }

    return api.post('/register/resend-otp', data);
};

/*
| Login
*/
export const login = async (email, password) => {
    await csrf();

    return api.post('/login', {
        email: email,
        password: password,
    });
};

/*
| Current Logged-in User
*/
export const getUser = async () => {
    return api.get('/user');
};

/*
| Logout
*/
export const logout = async () => {
    try {
        return await api.post('/logout');
    } finally {
        localStorage.removeItem('auth_user');
        localStorage.removeItem('registration_email');
        localStorage.removeItem('password_reset_email');
    }
};

/*
|--------------------------------------------------------------------------
| FORGOT PASSWORD
|--------------------------------------------------------------------------
*/

/*
| Send Password Reset OTP
*/
export const forgotPassword = async (email) => {
    await csrf();

    return api.post('/forgot-password', {
        email: email,
    });
};

/*
| Verify Password Reset OTP
*/
export const verifyPasswordResetOtp = async (otp) => {
    return api.post('/forgot-password/verify-otp', {
        otp: otp,
    });
};

/*
| Resend Password Reset OTP
*/
export const resendPasswordResetOtp = async (email = null) => {
    const data = {};

    if (email) {
        data.email = email;
    }

    return api.post('/forgot-password/resend-otp', data);
};

/*
| Reset Password
*/
export const resetPassword = async (
    password,
    passwordConfirmation
) => {
    return api.post('/reset-password', {
        password: password,
        password_confirmation: passwordConfirmation,
    });
};

/*
|--------------------------------------------------------------------------
| PROFILE PASSWORD
|--------------------------------------------------------------------------
*/

/*
| Change Password
*/
export const changePassword = async (
    currentPassword,
    password,
    passwordConfirmation
) => {
    return api.post('/profile/password', {
        current_password: currentPassword,
        password: password,
        password_confirmation: passwordConfirmation,
    });
};

/*
|--------------------------------------------------------------------------
| LOCAL USER HELPERS
|--------------------------------------------------------------------------
*/

/*
| Save User
*/
export const saveUser = (user) => {
    if (user) {
        localStorage.setItem(
            'auth_user',
            JSON.stringify(user)
        );
    }
};

/*
| Get Saved User
*/
export const getStoredUser = () => {
    const user = localStorage.getItem('auth_user');

    if (!user) {
        return null;
    }

    try {
        return JSON.parse(user);
    } catch (error) {
        console.error('Invalid stored user:', error);

        localStorage.removeItem('auth_user');

        return null;
    }
};

/*
| Clear Saved User
*/
export const clearAuth = () => {
    localStorage.removeItem('auth_user');
    localStorage.removeItem('registration_email');
    localStorage.removeItem('password_reset_email');
};

/*
|--------------------------------------------------------------------------
| Export Axios Instance
|--------------------------------------------------------------------------
*/

export default api;