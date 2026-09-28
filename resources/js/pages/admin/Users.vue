<template>
    <div class="users-page">
        <div class="page-header">
            <div>
                <h1>Manage Users</h1>
                <p>View, activate, deactivate and manage registered users.</p>
            </div>

```
        <button class="btn btn-primary" @click="openAddUser">
            + Add User
        </button>
    </div>

    <div v-if="successMessage" class="alert success">
        {{ successMessage }}
    </div>

    <div v-if="errorMessage" class="alert danger">
        {{ errorMessage }}
    </div>

    <div class="toolbar">
        <input
            v-model="search"
            type="text"
            placeholder="Search users..."
            class="search"
        >

        <strong>{{ filteredUsers.length }} users</strong>
    </div>

    <div v-if="loading" class="loading">
        Loading users...
    </div>

    <div v-else class="table-card">
        <table v-if="filteredUsers.length" class="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(user, index) in filteredUsers" :key="user.id">
                    <td>{{ index + 1 }}</td>

                    <td>
                        <div class="user-info">
                            <div class="avatar">
                                {{ initials(user.name) }}
                            </div>

                            <div>
                                <strong>{{ user.name }}</strong>
                                <small>ID: {{ user.id }}</small>
                            </div>
                        </div>
                    </td>

                    <td>{{ user.email }}</td>

                    <td>
                        <span
                            class="badge"
                            :class="user.role === 'admin'
                                ? 'admin'
                                : 'user'"
                        >
                            {{ user.role }}
                        </span>
                    </td>

                    <td>
                        <span
                            class="badge"
                            :class="isActive(user)
                                ? 'active'
                                : 'inactive'"
                        >
                            {{ isActive(user) ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td>
                        {{ formatDate(user.created_at) }}
                    </td>

                    <td>
                        <div class="actions">
                            <button
                                v-if="!isActive(user)"
                                class="action activate"
                                @click="activateUser(user)"
                            >
                                Activate
                            </button>

                            <button
                                v-else
                                class="action deactivate"
                                :disabled="isCurrentUser(user)"
                                @click="deactivateUser(user)"
                            >
                                Deactivate
                            </button>

                            <button
                                class="action password"
                                @click="changePassword(user)"
                            >
                                Password
                            </button>

                            <button
                                class="action delete"
                                :disabled="isCurrentUser(user)"
                                @click="deleteUser(user)"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else class="empty">
            <h3>No users found</h3>
            <p>There are no users matching your search.</p>
        </div>
    </div>

    <!-- Add User Modal -->
    <div
        v-if="showModal"
        class="modal-overlay"
        @click.self="closeModal"
    >
        <div class="modal">
            <div class="modal-header">
                <div>
                    <h2>Add User</h2>
                    <p>Create a new user or administrator.</p>
                </div>

                <button class="close" @click="closeModal">
                    ×
                </button>
            </div>

            <form @submit.prevent="createUser">
                <div class="form-group">
                    <label>Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="Enter name"
                    >
                    <small v-if="formErrors.name">
                        {{ formErrors.name }}
                    </small>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="Enter email"
                    >
                    <small v-if="formErrors.email">
                        {{ formErrors.email }}
                    </small>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <select v-model="form.role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        minlength="8"
                        required
                        placeholder="Minimum 8 characters"
                    >
                    <small v-if="formErrors.password">
                        {{ formErrors.password }}
                    </small>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        minlength="8"
                        required
                        placeholder="Confirm password"
                    >
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        @click="closeModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        :disabled="creating"
                    >
                        {{ creating ? 'Creating...' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api, { csrf } from '../../services/api';

const users = ref([]);
const loading = ref(false);
const creating = ref(false);

const search = ref('');

const successMessage = ref('');
const errorMessage = ref('');

const currentUser = ref(null);

const showModal = ref(false);

const form = reactive({
    name: '',
    email: '',
    role: 'user',
    password: '',
    password_confirmation: '',
});

const formErrors = reactive({});

const filteredUsers = computed(() => {
    const keyword = search.value.trim().toLowerCase();

    if (!keyword) {
        return users.value;
    }

    return users.value.filter(user =>
        String(user.name || '').toLowerCase().includes(keyword) ||
        String(user.email || '').toLowerCase().includes(keyword) ||
        String(user.role || '').toLowerCase().includes(keyword)
    );
});

async function loadUsers() {
    loading.value = true;
    errorMessage.value = '';

    try {
        const response = await api.get('/admin/users');

        console.log('Users API:', response.data);

        users.value = Array.isArray(response.data.users)
            ? response.data.users
            : [];

    } catch (error) {
        console.error('Users error:', error);

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to load users.';

        users.value = [];
    } finally {
        loading.value = false;
    }
}

async function loadCurrentUser() {
    try {
        const response = await api.get('/user');

        if (response.data?.success) {
            currentUser.value = response.data.user;
        }
    } catch (error) {
        console.error(error);
    }
}

function openAddUser() {
    resetForm();
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    resetForm();
}

function resetForm() {
    form.name = '';
    form.email = '';
    form.role = 'user';
    form.password = '';
    form.password_confirmation = '';

    Object.keys(formErrors).forEach(key => {
        delete formErrors[key];
    });
}

async function createUser() {
    clearMessages();

    Object.keys(formErrors).forEach(key => {
        delete formErrors[key];
    });

    if (form.password !== form.password_confirmation) {
        formErrors.password = 'Passwords do not match.';
        return;
    }

    creating.value = true;

    try {
        await csrf();

        const response = await api.post('/admin/users', form);

        successMessage.value =
            response.data?.message ||
            'User created successfully.';

        closeModal();

        await loadUsers();

    } catch (error) {
        const errors = error.response?.data?.errors;

        if (errors) {
            Object.keys(errors).forEach(key => {
                formErrors[key] = Array.isArray(errors[key])
                    ? errors[key][0]
                    : errors[key];
            });
        }

        errorMessage.value =
            error.response?.data?.message ||
            'Unable to create user.';

    } finally {
        creating.value = false;
    }
}

async function activateUser(user) {
    clearMessages();

    try {
        await csrf();

        const response = await api.patch(
            `/admin/users/${user.id}/activate`
        );

        successMessage.value =
            response.data?.message ||
            'User activated successfully.';

        await loadUsers();

    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Unable to activate user.';
    }
}

async function deactivateUser(user) {
    if (isCurrentUser(user)) {
        errorMessage.value =
            'You cannot deactivate your own account.';
        return;
    }

    if (!window.confirm(`Deactivate ${user.name}?`)) {
        return;
    }

    clearMessages();

    try {
        await csrf();

        const response = await api.patch(
            `/admin/users/${user.id}/deactivate`
        );

        successMessage.value =
            response.data?.message ||
            'User deactivated successfully.';

        await loadUsers();

    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Unable to deactivate user.';
    }
}

async function deleteUser(user) {
    if (isCurrentUser(user)) {
        errorMessage.value =
            'You cannot delete your own account.';
        return;
    }

    if (!window.confirm(`Delete ${user.name}?`)) {
        return;
    }

    clearMessages();

    try {
        await csrf();

        const response = await api.delete(
            `/admin/users/${user.id}`
        );

        successMessage.value =
            response.data?.message ||
            'User deleted successfully.';

        await loadUsers();

    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Unable to delete user.';
    }
}

async function changePassword(user) {
    if (!window.confirm(`Change password for ${user.name}?`)) {
        return;
    }

    clearMessages();

    try {
        await csrf();

        const response = await api.post(
            `/admin/users/${user.id}/change-password`
        );

        successMessage.value =
            response.data?.message ||
            'Password changed successfully.';

    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Unable to change password.';
    }
}

function isActive(user) {
    return (
        user.is_active === true ||
        user.is_active === 1 ||
        user.is_active === '1'
    );
}

function isCurrentUser(user) {
    return Number(currentUser.value?.id) === Number(user.id);
}

function initials(name) {
    if (!name) {
        return 'U';
    }

    const parts = name.trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }

    return (
        parts[0][0] +
        parts[parts.length - 1][0]
    ).toUpperCase();
}

function formatDate(date) {
    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

function clearMessages() {
    successMessage.value = '';
    errorMessage.value = '';
}

onMounted(async () => {
    await Promise.all([
        loadUsers(),
        loadCurrentUser(),
    ]);
});
</script>

<style scoped>
.users-page {
    width: 100%;
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0 0 6px;
    font-size: 30px;
    font-weight: 700;
}

.page-header p {
    margin: 0;
    color: #6c757d;
}

.btn {
    border: 0;
    border-radius: 8px;
    padding: 11px 18px;
    cursor: pointer;
    font-weight: 600;
}

.btn-primary {
    background: #0d6efd;
    color: white;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.success {
    background: #d1e7dd;
    color: #0f5132;
}

.danger {
    background: #f8d7da;
    color: #842029;
}

.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    gap: 20px;
}

.search {
    width: 450px;
    max-width: 100%;
    padding: 12px 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    outline: none;
}

.loading {
    padding: 60px;
    text-align: center;
    background: white;
    border-radius: 12px;
}

.table-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow-x: auto;
}

.users-table {
    width: 100%;
    min-width: 1000px;
    border-collapse: collapse;
}

.users-table th {
    padding: 15px;
    background: #f8f9fa;
    text-align: left;
    font-size: 13px;
}

.users-table td {
    padding: 15px;
    border-top: 1px solid #eee;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-info small {
    display: block;
    color: #999;
    margin-top: 3px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge.admin {
    background: #e7dff8;
    color: #59359a;
}

.badge.user {
    background: #e9ecef;
    color: #495057;
}

.badge.active {
    background: #d1e7dd;
    color: #0f5132;
}

.badge.inactive {
    background: #f8d7da;
    color: #842029;
}

.actions {
    display: flex;
    gap: 6px;
}

.action {
    border: 0;
    border-radius: 6px;
    padding: 7px 9px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
}

.action.activate {
    background: #d1e7dd;
    color: #0f5132;
}

.action.deactivate {
    background: #fff3cd;
    color: #664d03;
}

.action.password {
    background: #cfe2ff;
    color: #084298;
}

.action.delete {
    background: #f8d7da;
    color: #842029;
}

.action:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.empty {
    padding: 70px 20px;
    text-align: center;
}

.empty h3 {
    margin-bottom: 8px;
}

.empty p {
    color: #6c757d;
}

/* Modal */

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, .45);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal {
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    background: white;
    border-radius: 14px;
}

.modal-header {
    padding: 22px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
}

.modal-header h2 {
    margin: 0 0 5px;
}

.modal-header p {
    margin: 0;
    color: #6c757d;
    font-size: 13px;
}

.close {
    border: 0;
    background: #f8f9fa;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    font-size: 22px;
    cursor: pointer;
}

.modal form {
    padding: 22px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: 600;
    font-size: 14px;
}

.form-group input,
.form-group select {
    width: 100%;
    box-sizing: border-box;
    padding: 11px;
    border: 1px solid #ced4da;
    border-radius: 7px;
}

.form-group small {
    display: block;
    color: #dc3545;
    margin-top: 5px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    margin-top: 20px;
    border-top: 1px solid #eee;
}

@media (max-width: 768px) {
    .users-page {
        padding: 20px 15px;
    }

    .page-header {
        flex-direction: column;
        gap: 15px;
    }

    .page-header .btn {
        width: 100%;
    }

    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .search {
        width: 100%;
    }
}
</style>
