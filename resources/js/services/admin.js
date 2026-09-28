import api from './api';

/*
|--------------------------------------------------------------------------
| Admin Dashboard
|--------------------------------------------------------------------------
*/

export async function getAdminDashboard() {
    return api.get('/admin/dashboard');
}

/*
|--------------------------------------------------------------------------
| Admin Profile
|--------------------------------------------------------------------------
*/

export async function getAdminProfile() {
    return api.get('/admin/profile');
}

export async function updateAdminProfile(data) {
    return api.put('/admin/profile', data);
}

/*
|--------------------------------------------------------------------------
| Admin Theme
|--------------------------------------------------------------------------
*/

export async function getAdminTheme() {
    return api.get('/admin/profile/theme');
}

export async function updateAdminTheme(data) {
    return api.put('/admin/profile/theme', data);
}

/*
|--------------------------------------------------------------------------
| Admin Password
|--------------------------------------------------------------------------
*/

export async function changeAdminPassword(data) {
    return api.put('/admin/profile/password', data);
}

/*
|--------------------------------------------------------------------------
| Admin Users
|--------------------------------------------------------------------------
*/

export async function getAdminUsers(params = {}) {
    return api.get('/admin/users', {
        params,
    });
}

export async function getAdminUser(id) {
    return api.get(`/admin/users/${id}`);
}

export async function createAdminUser(data) {
    return api.post('/admin/users', data);
}

export async function updateAdminUser(id, data) {
    return api.put(`/admin/users/${id}`, data);
}

export async function deleteAdminUser(id) {
    return api.delete(`/admin/users/${id}`);
}

/*
|--------------------------------------------------------------------------
| Admin User Status
|--------------------------------------------------------------------------
*/

export async function toggleAdminUserStatus(id) {
    return api.patch(`/admin/users/${id}/status`);
}

export async function activateAdminUser(id) {
    return api.patch(`/admin/users/${id}/activate`);
}

export async function deactivateAdminUser(id) {
    return api.patch(`/admin/users/${id}/deactivate`);
}

/*
|--------------------------------------------------------------------------
| Generate User Password
|--------------------------------------------------------------------------
*/

export async function changeAdminUserPassword(id) {
    return api.post(`/admin/users/${id}/change-password`);
}