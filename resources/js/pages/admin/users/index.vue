<template>
    <div class="users-page">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>Users</h1>
                <p>Manage users registered in the database.</p>
            </div>

            <button class="add-btn" @click="openCreateModal">
                + Add User
            </button>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon">👥</div>

                <div>
                    <span>Total Users</span>
                    <strong>{{ statistics.total }}</strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👑</div>

                <div>
                    <span>Admins</span>
                    <strong>{{ statistics.admins }}</strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">✓</div>

                <div>
                    <span>Active</span>
                    <strong>{{ statistics.active }}</strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">○</div>

                <div>
                    <span>Inactive</span>
                    <strong>{{ statistics.inactive }}</strong>
                </div>
            </div>

        </div>

        <!-- Search / Filter -->
        <div class="filter-card">

            <div class="search-box">
                <span>🔍</span>

                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name or email..."
                    @keyup.enter="loadUsers"
                />
            </div>

            <select
                v-model="role"
                @change="loadUsers"
            >
                <option value="">All Roles</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <button
                class="search-btn"
                @click="loadUsers"
            >
                Search
            </button>

            <button
                class="reset-btn"
                @click="resetFilters"
            >
                Reset
            </button>

        </div>

        <!-- Loading -->
        <div
            v-if="loading"
            class="loading"
        >
            <div class="spinner"></div>
            <p>Loading users...</p>
        </div>

        <!-- Error -->
        <div
            v-else-if="error"
            class="error-message"
        >
            <strong>{{ error }}</strong>

            <button @click="loadUsers">
                Try Again
            </button>
        </div>

        <!-- Users Table -->
        <div
            v-else
            class="table-card"
        >

            <div class="table-header">
                <div>
                    <h2>All Users</h2>

                    <p>
                        {{ pagination.total }} users found
                    </p>
                </div>
            </div>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Email Verification</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr
                            v-for="(user, index) in users"
                            :key="user.id"
                        >

                            <!-- Number -->
                            <td>
                                {{
                                    (pagination.current_page - 1)
                                    * pagination.per_page
                                    + index
                                    + 1
                                }}
                            </td>

                            <!-- User -->
                            <td>

                                <div class="user-info">

                                    <div class="avatar">
                                        {{ getInitials(user.name) }}
                                    </div>

                                    <div>
                                        <strong>
                                            {{ user.name }}
                                        </strong>

                                        <small>
                                            ID: {{ user.id }}
                                        </small>
                                    </div>

                                </div>

                            </td>

                            <!-- Email -->
                            <td>
                                {{ user.email }}
                            </td>

                            <!-- Role -->
                            <td>

                                <span
                                    class="role-badge"
                                    :class="user.role"
                                >
                                    {{ user.role }}
                                </span>

                            </td>

                            <!-- Status -->
                            <td>

                                <span
                                    class="status-badge"
                                    :class="
                                        user.is_active
                                            ? 'active'
                                            : 'inactive'
                                    "
                                >
                                    {{
                                        user.is_active
                                            ? 'Active'
                                            : 'Inactive'
                                    }}
                                </span>

                            </td>

                            <!-- Email Verification -->
                            <td>

                                <span
                                    v-if="user.email_verified_at"
                                    class="verified"
                                >
                                    ✓ Verified
                                </span>

                                <span
                                    v-else
                                    class="not-verified"
                                >
                                    Not Verified
                                </span>

                            </td>

                            <!-- Registered -->
                            <td>
                                {{ formatDate(user.created_at) }}
                            </td>

                            <!-- Actions -->
                            <td>

                                <div class="actions">

                                    <!-- Activate / Deactivate -->
                                    <button
                                        class="action status"
                                        :title="
                                            user.is_active
                                                ? 'Deactivate User'
                                                : 'Activate User'
                                        "
                                        @click="askToggleStatus(user)"
                                    >
                                        {{
                                            user.is_active
                                                ? '⏸'
                                                : '▶'
                                        }}
                                    </button>

                                    <!-- Change Password -->
                                    <button
                                        class="action password"
                                        title="Generate New Password"
                                        @click="askChangePassword(user)"
                                        :disabled="
                                            passwordLoadingId === user.id
                                        "
                                    >
                                        {{
                                            passwordLoadingId === user.id
                                                ? '...'
                                                : '🔑'
                                        }}
                                    </button>

                                    <!-- Delete -->
                                    <button
                                        class="action delete"
                                        title="Delete User"
                                        @click="askDelete(user)"
                                    >
                                        🗑
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <!-- No users -->
                        <tr v-if="users.length === 0">

                            <td
                                colspan="8"
                                class="empty"
                            >
                                No users found.
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

            <!-- Pagination -->
            <div
                v-if="pagination.last_page > 1"
                class="pagination"
            >

                <button
                    :disabled="
                        pagination.current_page <= 1
                    "
                    @click="
                        goToPage(
                            pagination.current_page - 1
                        )
                    "
                >
                    ← Previous
                </button>

                <span>
                    Page {{ pagination.current_page }}
                    of {{ pagination.last_page }}
                </span>

                <button
                    :disabled="
                        pagination.current_page >=
                        pagination.last_page
                    "
                    @click="
                        goToPage(
                            pagination.current_page + 1
                        )
                    "
                >
                    Next →
                </button>

            </div>

        </div>

        <!-- ADD USER MODAL -->
        <div
            v-if="showFormModal"
            class="modal-overlay"
            @click.self="closeModal"
        >

            <div class="modal">

                <div class="modal-header">

                    <h2>Add User</h2>

                    <button
                        type="button"
                        @click="closeModal"
                    >
                        ×
                    </button>

                </div>

                <form @submit.prevent="saveUser">

                    <div class="form-group">

                        <label>Name</label>

                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter user name"
                            required
                        />

                    </div>

                    <div class="form-group">

                        <label>Email</label>

                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter user email"
                            required
                        />

                    </div>

                    <div class="form-group">

                        <label>Role</label>

                        <select v-model="form.role">

                            <option value="user">
                                User
                            </option>

                            <option value="admin">
                                Admin
                            </option>

                        </select>

                    </div>

                    <div class="form-group">

                        <label>Password</label>

                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="Minimum 8 characters"
                            required
                        />

                    </div>

                    <div class="form-group">

                        <label>Confirm Password</label>

                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Confirm password"
                            required
                        />

                    </div>

                    <div
                        v-if="formError"
                        class="form-error"
                    >
                        {{ formError }}
                    </div>

                    <div class="modal-footer">

                        <button
                            type="button"
                            class="cancel-btn"
                            @click="closeModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="save-btn"
                            :disabled="saving"
                        >
                            {{
                                saving
                                    ? 'Creating...'
                                    : 'Create User'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

        <!-- =====================================================
             DELETE CONFIRMATION
        ===================================================== -->
        <ConfirmModal
            v-model="showDeleteModal"
            type="danger"
            title="Delete User?"
            :message="`Are you sure you want to permanently delete ${selectedUser?.name || 'this user'}? This action cannot be undone.`"
            confirm-text="Delete"
            cancel-text="Cancel"
            :loading="deleting"
            @confirm="confirmDelete"
        />

        <!-- =====================================================
             STATUS TOGGLE CONFIRMATION
        ===================================================== -->
        <ConfirmModal
            v-model="showStatusModal"
            :type="statusAction === 'deactivate' ? 'warning' : 'success'"
            :title="statusAction === 'deactivate' ? 'Deactivate User?' : 'Activate User?'"
            :message="statusAction === 'deactivate'
                ? `${selectedUser?.name || 'This user'} will not be able to log in until reactivated.`
                : `${selectedUser?.name || 'This user'} will be able to log in again.`"
            :confirm-text="statusAction === 'deactivate' ? 'Deactivate' : 'Activate'"
            cancel-text="Cancel"
            :loading="togglingStatus"
            @confirm="confirmToggleStatus"
        />

        <!-- =====================================================
             CHANGE PASSWORD CONFIRMATION
        ===================================================== -->
        <ConfirmModal
            v-model="showPasswordModal"
            type="warning"
            title="Generate New Password?"
            :message="`A new random password will be generated for ${selectedUser?.name || 'this user'} and sent to ${selectedUser?.email || 'their email'}.`"
            confirm-text="Generate"
            cancel-text="Cancel"
            :loading="changingPassword"
            @confirm="confirmChangePassword"
        />

    </div>
</template>


<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
} from 'vue';

import {
    getAdminUsers,
    createAdminUser,
    deleteAdminUser,
    toggleAdminUserStatus,
    changeAdminUserPassword,
} from '../../../services/admin';

import ConfirmModal from '../../../components/ConfirmModal.vue';

/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const users = ref([]);

const loading = ref(false);
const saving = ref(false);

const error = ref('');
const formError = ref('');

const search = ref('');
const role = ref('');

const showFormModal = ref(false);

const passwordLoadingId = ref(null);

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
});

const form = reactive({
    name: '',
    email: '',
    role: 'user',
    password: '',
    password_confirmation: '',
});

/*
|--------------------------------------------------------------------------
| CONFIRM MODAL STATE
|--------------------------------------------------------------------------
*/

const showDeleteModal = ref(false);
const showStatusModal = ref(false);
const showPasswordModal = ref(false);

const selectedUser = ref(null);
const statusAction = ref('deactivate');

const deleting = ref(false);
const togglingStatus = ref(false);
const changingPassword = ref(false);

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const statistics = computed(() => {

    const allUsers = users.value;

    return {
        total: pagination.total,

        admins: allUsers.filter(
            user => user.role === 'admin'
        ).length,

        active: allUsers.filter(
            user => Boolean(user.is_active)
        ).length,

        inactive: allUsers.filter(
            user => !Boolean(user.is_active)
        ).length,
    };
});

/*
|--------------------------------------------------------------------------
| Load Users
|--------------------------------------------------------------------------
*/

async function loadUsers(page = 1) {

    loading.value = true;
    error.value = '';

    try {

        const response = await getAdminUsers({
            search: search.value || undefined,
            role: role.value || undefined,
            page,
            per_page: pagination.per_page,
        });

        const data = response.data;

        const paginated = data.users;

        users.value = paginated?.data ?? [];

        pagination.current_page =
            paginated?.current_page ?? 1;

        pagination.last_page =
            paginated?.last_page ?? 1;

        pagination.per_page =
            paginated?.per_page ?? 15;

        pagination.total =
            paginated?.total ?? users.value.length;

    } catch (err) {

        console.error(
            'Load users error:',
            err
        );

        error.value =
            err.response?.data?.message ||
            'Unable to load users.';

    } finally {

        loading.value = false;

    }
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function goToPage(page) {

    if (
        page < 1 ||
        page > pagination.last_page
    ) {
        return;
    }

    loadUsers(page);
}

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

function resetFilters() {

    search.value = '';
    role.value = '';

    loadUsers(1);
}

/*
|--------------------------------------------------------------------------
| Add User
|--------------------------------------------------------------------------
*/

function openCreateModal() {

    form.name = '';
    form.email = '';
    form.role = 'user';
    form.password = '';
    form.password_confirmation = '';

    formError.value = '';

    showFormModal.value = true;
}

/*
|--------------------------------------------------------------------------
| Create User
|--------------------------------------------------------------------------
*/

async function saveUser() {

    saving.value = true;
    formError.value = '';

    try {

        await createAdminUser({
            name: form.name,
            email: form.email,
            role: form.role,
            password: form.password,
            password_confirmation:
                form.password_confirmation,
        });

        closeModal();

        await loadUsers(
            pagination.current_page
        );

    } catch (err) {

        console.error(
            'Create user error:',
            err
        );

        if (
            err.response?.data?.errors
        ) {

            const errors =
                err.response.data.errors;

            const firstError =
                Object.values(errors)[0];

            formError.value =
                Array.isArray(firstError)
                    ? firstError[0]
                    : 'Validation error.';

        } else {

            formError.value =
                err.response?.data?.message ||
                'Unable to create user.';

        }

    } finally {

        saving.value = false;

    }
}

/*
|--------------------------------------------------------------------------
| Toggle Status — ask confirmation
|--------------------------------------------------------------------------
*/

function askToggleStatus(user) {

    selectedUser.value = user;

    statusAction.value = user.is_active
        ? 'deactivate'
        : 'activate';

    showStatusModal.value = true;
}

/*
|--------------------------------------------------------------------------
| Toggle Status — confirm
|--------------------------------------------------------------------------
*/

async function confirmToggleStatus() {

    if (!selectedUser.value) return;

    togglingStatus.value = true;

    try {

        await toggleAdminUserStatus(
            selectedUser.value.id
        );

        showStatusModal.value = false;

        await loadUsers(
            pagination.current_page
        );

    } catch (err) {

        console.error(
            'Status error:',
            err
        );

        alert(
            err.response?.data?.message ||
            'Unable to change user status.'
        );

    } finally {

        togglingStatus.value = false;
        selectedUser.value = null;

    }
}

/*
|--------------------------------------------------------------------------
| Change Password — ask confirmation
|--------------------------------------------------------------------------
*/

function askChangePassword(user) {

    selectedUser.value = user;

    showPasswordModal.value = true;
}

/*
|--------------------------------------------------------------------------
| Change Password — confirm
|--------------------------------------------------------------------------
*/

async function confirmChangePassword() {

    if (!selectedUser.value) return;

    const user = selectedUser.value;

    changingPassword.value = true;
    passwordLoadingId.value = user.id;

    try {

        const response =
            await changeAdminUserPassword(
                user.id
            );

        showPasswordModal.value = false;

        alert(
            response.data?.message ||
            `New password has been generated and sent to ${user.email}.`
        );

    } catch (err) {

        console.error(
            'Change password error:',
            err
        );

        alert(
            err.response?.data?.message ||
            'Unable to change password.'
        );

    } finally {

        changingPassword.value = false;
        passwordLoadingId.value = null;
        selectedUser.value = null;

    }
}

/*
|--------------------------------------------------------------------------
| Delete User — ask confirmation
|--------------------------------------------------------------------------
*/

function askDelete(user) {

    selectedUser.value = user;

    showDeleteModal.value = true;
}

/*
|--------------------------------------------------------------------------
| Delete User — confirm
|--------------------------------------------------------------------------
*/

async function confirmDelete() {

    if (!selectedUser.value) return;

    deleting.value = true;

    try {

        await deleteAdminUser(
            selectedUser.value.id
        );

        showDeleteModal.value = false;

        await loadUsers(
            pagination.current_page
        );

    } catch (err) {

        console.error(
            'Delete user error:',
            err
        );

        alert(
            err.response?.data?.message ||
            'Unable to delete user.'
        );

    } finally {

        deleting.value = false;
        selectedUser.value = null;

    }
}

/*
|--------------------------------------------------------------------------
| Close Modal
|--------------------------------------------------------------------------
*/

function closeModal() {

    showFormModal.value = false;

    formError.value = '';

}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function getInitials(name) {

    if (!name) {
        return 'U';
    }

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map(
            word =>
                word
                    .charAt(0)
                    .toUpperCase()
        )
        .join('');
}

function formatDate(date) {

    if (!date) {
        return '-';
    }

    return new Date(date).toLocaleDateString(
        'en-US',
        {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        }
    );
}

/*
|--------------------------------------------------------------------------
| Initial Load
|--------------------------------------------------------------------------
*/

onMounted(() => {
    loadUsers();
});
</script>


<style scoped>
.users-page {
    padding: 28px;
    max-width: 1600px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    margin: 0 0 6px;
    font-size: 30px;
    color: #1f2937;
}

.page-header p {
    margin: 0;
    color: #6b7280;
}

.add-btn,
.search-btn,
.save-btn {
    border: none;
    padding: 11px 18px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.add-btn {
    background: #111827;
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 22px;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
}

.stat-card span {
    display: block;
    color: #6b7280;
    font-size: 13px;
    margin-bottom: 5px;
}

.stat-card strong {
    font-size: 24px;
    color: #111827;
}

.filter-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 10px;
    margin-bottom: 22px;
}

.search-box {
    flex: 1;
    display: flex;
    align-items: center;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0 12px;
}

.search-box input {
    width: 100%;
    border: none;
    outline: none;
    padding: 11px;
}

.filter-card select {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 0 12px;
}

.search-btn {
    background: #111827;
    color: white;
}

.reset-btn {
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    padding: 0 16px;
    cursor: pointer;
}

.table-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.table-header h2 {
    margin: 0 0 4px;
}

.table-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
    white-space: nowrap;
}

th {
    background: #f9fafb;
    color: #6b7280;
    font-size: 13px;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #111827;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.user-info strong {
    display: block;
}

.user-info small {
    color: #9ca3af;
}

.role-badge,
.status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-badge.admin {
    background: #ede9fe;
    color: #6d28d9;
}

.role-badge.user {
    background: #e0f2fe;
    color: #0369a1;
}

.status-badge.active {
    background: #dcfce7;
    color: #15803d;
}

.status-badge.inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.verified {
    color: #15803d;
    font-size: 13px;
}

.not-verified {
    color: #b45309;
    font-size: 13px;
}

.actions {
    display: flex;
    gap: 6px;
}

.action {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    background: #f3f4f6;
}

.action:hover {
    background: #e5e7eb;
}

.action:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.empty {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.loading {
    background: white;
    padding: 60px;
    text-align: center;
    border-radius: 12px;
}

.spinner {
    width: 35px;
    height: 35px;
    border: 4px solid #e5e7eb;
    border-top-color: #111827;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: auto;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.error-message {
    background: #fee2e2;
    color: #991b1b;
    padding: 20px;
    border-radius: 10px;
}

.error-message button {
    margin-left: 15px;
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    padding: 20px;
}

.pagination button {
    padding: 9px 14px;
    border: 1px solid #d1d5db;
    background: white;
    border-radius: 7px;
    cursor: pointer;
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
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
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.modal-header h2 {
    margin: 0;
}

.modal-header button {
    border: none;
    background: transparent;
    font-size: 25px;
    cursor: pointer;
}

form {
    padding: 20px;
}

.form-group {
    margin-bottom: 16px;
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
    padding: 11px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #111827;
}

.form-error {
    background: #fee2e2;
    color: #991b1b;
    padding: 10px;
    border-radius: 7px;
    margin-bottom: 15px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 10px;
}

.cancel-btn {
    padding: 10px 17px;
    border: none;
    background: #f3f4f6;
    border-radius: 7px;
    cursor: pointer;
}

.save-btn {
    background: #111827;
    color: white;
}

.save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 1000px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {

    .users-page {
        padding: 15px;
    }

    .page-header {
        align-items: flex-start;
        gap: 15px;
    }

    .filter-card {
        flex-direction: column;
    }

    .filter-card select,
    .search-btn,
    .reset-btn {
        height: 42px;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>