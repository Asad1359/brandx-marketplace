import axios from 'axios';

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
| CSRF
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
| Axios Request Interceptor
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
| Axios Response Interceptor
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
| REGISTER
|--------------------------------------------------------------------------
*/

export const register = async (data) => {
    await csrf();

    return api.post('/register', {
        name: data.name,
        email: data.email,
        password: data.password,

        password_confirmation:
            data.password_confirmation ||
            data.passwordConfirmation,
    });
};


/*
|--------------------------------------------------------------------------
| REGISTER OTP
|--------------------------------------------------------------------------
*/

export const verifyRegistrationOtp = async (otp) => {
    return api.post('/register/verify-otp', {
        otp,
    });
};


/*
|--------------------------------------------------------------------------
| RESEND REGISTER OTP
|--------------------------------------------------------------------------
*/

export const resendRegistrationOtp = async (email = null) => {
    const data = {};

    if (email) {
        data.email = email;
    }

    return api.post('/register/resend-otp', data);
};


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

export const login = async (email, password) => {
    await csrf();

    return api.post('/login', {
        email,
        password,
    });
};


/*
|--------------------------------------------------------------------------
| CURRENT USER
|--------------------------------------------------------------------------
*/

export const getUser = async () => {
    return api.get('/user');
};


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
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

export const forgotPassword = async (email) => {
    await csrf();

    return api.post('/forgot-password', {
        email,
    });
};


/*
|--------------------------------------------------------------------------
| PASSWORD OTP
|--------------------------------------------------------------------------
*/

export const verifyPasswordResetOtp = async (otp) => {
    return api.post('/forgot-password/verify-otp', {
        otp,
    });
};


/*
|--------------------------------------------------------------------------
| PASSWORD OTP ALIAS
|--------------------------------------------------------------------------
| password-otp.vue can use verifyPasswordOtp
|--------------------------------------------------------------------------
*/

export const verifyPasswordOtp = verifyPasswordResetOtp;


/*
|--------------------------------------------------------------------------
| RESEND PASSWORD OTP
|--------------------------------------------------------------------------
*/

export const resendPasswordResetOtp = async (email = null) => {
    const data = {};

    if (email) {
        data.email = email;
    }

    return api.post('/forgot-password/resend-otp', data);
};


/*
|--------------------------------------------------------------------------
| RESEND PASSWORD OTP ALIAS
|--------------------------------------------------------------------------
| password-otp.vue can use resendPasswordOtp
|--------------------------------------------------------------------------
*/

export const resendPasswordOtp = resendPasswordResetOtp;


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

export const resetPassword = async (
    password,
    passwordConfirmation
) => {
    return api.post('/reset-password', {
        password,
        password_confirmation: passwordConfirmation,
    });
};


/*
|--------------------------------------------------------------------------
| CHANGE PASSWORD
|--------------------------------------------------------------------------
*/

export const changePassword = async (
    currentPassword,
    password,
    passwordConfirmation
) => {
    return api.post('/profile/password', {
        current_password: currentPassword,
        password,
        password_confirmation: passwordConfirmation,
    });
};


/*
|--------------------------------------------------------------------------
| UPDATE PASSWORD ALIAS
|--------------------------------------------------------------------------
| change-password.vue is using updatePassword
|--------------------------------------------------------------------------
*/

export const updatePassword = changePassword;


/*
|--------------------------------------------------------------------------
| SAVE USER
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


/*
|--------------------------------------------------------------------------
| GET STORED USER
|--------------------------------------------------------------------------
*/

export const getStoredUser = () => {
    const user = localStorage.getItem('auth_user');

    if (!user) {
        return null;
    }

    try {
        return JSON.parse(user);
    } catch (error) {
        console.error(
            'Invalid stored user:',
            error
        );

        localStorage.removeItem('auth_user');

        return null;
    }
};


/*
|--------------------------------------------------------------------------
| CLEAR AUTH
|--------------------------------------------------------------------------
*/

export const clearAuth = () => {
    localStorage.removeItem('auth_user');
    localStorage.removeItem('registration_email');
    localStorage.removeItem('password_reset_email');
};


/*
|--------------------------------------------------------------------------
| EXPORT AXIOS
|--------------------------------------------------------------------------
*/

export default api;