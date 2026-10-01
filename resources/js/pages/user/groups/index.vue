<template>
    <div class="groups-page">

        <!-- =========================================================
             PAGE HEADER
        ========================================================== -->

        <div class="page-header">
            <div>
                <h1>My Groups</h1>
                <p>Manage and chat with your groups.</p>
            </div>

            <button
                type="button"
                class="create-btn"
                @click="openCreateModal"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Create Group
            </button>
        </div>


        <!-- =========================================================
             ERROR
        ========================================================== -->

        <div
            v-if="error"
            class="alert alert-danger"
        >
            {{ error }}
        </div>


        <!-- =========================================================
             LOADING
        ========================================================== -->

        <div
            v-if="loading"
            class="loading-state"
        >
            <div class="spinner"></div>
            <p>Loading groups...</p>
        </div>


        <!-- =========================================================
             EMPTY STATE
        ========================================================== -->

        <div
            v-else-if="groups.length === 0"
            class="empty-state"
        >
            <div class="empty-icon">👥</div>

            <h3>No Groups Yet</h3>

            <p>
                Create a group to start chatting with others.
            </p>

            <button
                type="button"
                class="create-btn"
                @click="openCreateModal"
            >
                Create Your First Group
            </button>
        </div>


        <!-- =========================================================
             GROUP LIST
        ========================================================== -->

        <div
            v-else
            class="groups-grid"
        >
            <router-link
                v-for="group in groups"
                :key="group.id"
                :to="`/dashboard/groups/${group.id}`"
                class="group-card"
            >
                <div class="group-avatar">
                    {{ group.name?.charAt(0)?.toUpperCase() || 'G' }}
                </div>

                <div class="group-info">

                    <h3>{{ group.name }}</h3>

                    <p>
                        {{ group.members_count ?? 0 }} members
                    </p>

                    <small v-if="group.last_message">
                        {{ truncate(group.last_message, 40) }}
                    </small>

                    <small v-else class="muted">
                        No messages yet
                    </small>

                </div>

                <i class="fa-solid fa-chevron-right arrow"></i>
            </router-link>
        </div>


        <!-- =========================================================
             CREATE GROUP MODAL
        ========================================================== -->

        <div
            v-if="showCreateModal"
            class="modal-overlay"
            @click.self="closeCreateModal"
        >
            <div class="modal">

                <div class="modal-header">
                    <h2>Create Group</h2>

                    <button
                        type="button"
                        class="close-btn"
                        @click="closeCreateModal"
                    >
                        ×
                    </button>
                </div>

                <form @submit.prevent="createGroup">

                    <div class="form-group">
                        <label>Group Name</label>

                        <input
                            v-model="newGroupName"
                            type="text"
                            placeholder="Enter group name"
                            maxlength="100"
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
                            @click="closeCreateModal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="save-btn"
                            :disabled="creating"
                        >
                            {{
                                creating
                                    ? 'Creating...'
                                    : 'Create Group'
                            }}
                        </button>

                    </div>

                </form>

            </div>
        </div>

    </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

import {
    getUserGroups,
    createGroup as createGroupApi,
} from '../../../services/chat';


const router = useRouter();


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const groups = ref([]);
const loading = ref(false);
const error = ref('');

const showCreateModal = ref(false);
const newGroupName = ref('');
const creating = ref(false);
const formError = ref('');


/*
|--------------------------------------------------------------------------
| LOAD GROUPS
|--------------------------------------------------------------------------
| GET /api/chat/groups
|--------------------------------------------------------------------------
*/

async function loadGroups() {
    loading.value = true;
    error.value = '';

    try {
        const response = await getUserGroups();

        if (response.data?.success) {
            groups.value = response.data.groups || [];
        } else {
            groups.value = [];
        }
    } catch (err) {
        console.error('Load groups error:', err);

        error.value =
            err.response?.data?.message ||
            'Unable to load groups.';
    } finally {
        loading.value = false;
    }
}


/*
|--------------------------------------------------------------------------
| CREATE GROUP — OPEN MODAL
|--------------------------------------------------------------------------
*/

function openCreateModal() {
    newGroupName.value = '';
    formError.value = '';
    showCreateModal.value = true;
}


/*
|--------------------------------------------------------------------------
| CREATE GROUP — CLOSE MODAL
|--------------------------------------------------------------------------
*/

function closeCreateModal() {
    showCreateModal.value = false;
    newGroupName.value = '';
    formError.value = '';
}


/*
|--------------------------------------------------------------------------
| CREATE GROUP — SUBMIT
|--------------------------------------------------------------------------
| POST /api/chat/groups
|--------------------------------------------------------------------------
*/

async function createGroup() {
    const name = newGroupName.value.trim();

    if (!name) {
        formError.value = 'Please enter a group name.';
        return;
    }

    creating.value = true;
    formError.value = '';

    try {
        const response = await createGroupApi(name);

        if (
            response.data?.success &&
            response.data?.group
        ) {
            closeCreateModal();

            // Navigate straight to the new group's chat
            router.push(
                `/dashboard/groups/${response.data.group.id}`
            );

        } else {
            formError.value =
                response.data?.message ||
                'Unable to create group.';
        }
    } catch (err) {
        console.error('Create group error:', err);

        formError.value =
            err.response?.data?.message ||
            'Unable to create group.';
    } finally {
        creating.value = false;
    }
}


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function truncate(text, length) {
    if (!text) return '';

    return text.length > length
        ? text.substring(0, length) + '...'
        : text;
}


/*
|--------------------------------------------------------------------------
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(() => {
    loadGroups();
});
</script>


<style scoped>

/* =========================================================
   PAGE
========================================================= */

.groups-page {
    padding: 24px;
    width: 100%;
    box-sizing: border-box;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 15px;
}

.page-header h1 {
    margin: 0 0 6px;
    color: #111827;
    font-size: 28px;
    font-weight: 700;
}

.page-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   CREATE BUTTON
========================================================= */

.create-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    padding: 11px 18px;

    border: none;
    border-radius: 10px;

    background: #4f46e5;
    color: #ffffff;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background 0.2s ease,
        transform 0.15s ease;
}

.create-btn:hover {
    background: #4338ca;
    transform: translateY(-1px);
}


/* =========================================================
   ALERTS
========================================================= */

.alert {
    padding: 13px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #b91c1c;
}


/* =========================================================
   LOADING
========================================================= */

.loading-state {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;

    padding: 60px 20px;

    color: #6b7280;
}

.spinner {
    width: 38px;
    height: 38px;

    border: 4px solid #e5e7eb;
    border-top-color: #4f46e5;
    border-radius: 50%;

    animation: spin 0.8s linear infinite;

    margin-bottom: 15px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;

    padding: 70px 20px;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 15px;

    text-align: center;
}

.empty-icon {
    font-size: 56px;
    margin-bottom: 12px;
}

.empty-state h3 {
    margin: 0 0 8px;
    color: #111827;
    font-size: 20px;
}

.empty-state p {
    margin: 0 0 22px;
    color: #6b7280;
    font-size: 14px;
}


/* =========================================================
   GROUPS GRID
========================================================= */

.groups-grid {
    display: grid;

    grid-template-columns:
        repeat(auto-fill, minmax(320px, 1fr));

    gap: 14px;
}

.group-card {
    display: flex;
    align-items: center;
    gap: 14px;

    padding: 16px 18px;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;

    text-decoration: none;
    color: inherit;

    transition:
        border-color 0.2s ease,
        transform 0.15s ease,
        box-shadow 0.2s ease;
}

.group-card:hover {
    border-color: #4f46e5;
    transform: translateY(-2px);
    box-shadow:
        0 8px 20px rgba(79, 70, 229, 0.10);
}

.group-avatar {
    width: 52px;
    height: 52px;
    flex-shrink: 0;

    border-radius: 50%;

    background:
        linear-gradient(135deg, #6366f1, #06b6d4);

    color: #ffffff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;
    font-weight: 700;
}

.group-info {
    flex: 1;
    min-width: 0;
}

.group-info h3 {
    margin: 0 0 4px;

    color: #111827;
    font-size: 15px;
    font-weight: 700;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.group-info p {
    margin: 0 0 4px;

    color: #4f46e5;
    font-size: 12px;
    font-weight: 600;
}

.group-info small {
    display: block;

    color: #6b7280;
    font-size: 12px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.group-info small.muted {
    color: #9ca3af;
    font-style: italic;
}

.arrow {
    color: #9ca3af;
    font-size: 13px;
    flex-shrink: 0;
}

.group-card:hover .arrow {
    color: #4f46e5;
}


/* =========================================================
   MODAL
========================================================= */

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
    max-width: 460px;

    background: #ffffff;
    border-radius: 14px;

    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.25);

    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 20px 22px;

    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.close-btn {
    width: 32px;
    height: 32px;

    border: none;
    border-radius: 50%;

    background: #f3f4f6;
    color: #6b7280;

    font-size: 20px;
    line-height: 1;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: background 0.15s ease;
}

.close-btn:hover {
    background: #e5e7eb;
    color: #111827;
}

.modal form {
    padding: 20px 22px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #374151;
    font-size: 13px;
    font-weight: 600;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;

    height: 46px;

    padding: 0 14px;

    border: 1px solid #d1d5db;
    border-radius: 8px;

    font-size: 14px;
    color: #111827;

    outline: none;

    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.form-group input:focus {
    border-color: #4f46e5;
    box-shadow:
        0 0 0 3px rgba(79, 70, 229, 0.12);
}

.form-error {
    padding: 10px 12px;

    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;

    color: #b91c1c;
    font-size: 13px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;

    margin-top: 20px;
    padding-top: 16px;

    border-top: 1px solid #f1f5f9;
}

.cancel-btn,
.save-btn {
    padding: 11px 18px;

    border: none;
    border-radius: 8px;

    font-size: 13px;
    font-weight: 600;

    cursor: pointer;

    transition: background 0.15s ease;
}

.cancel-btn {
    background: #f3f4f6;
    color: #374151;
}

.cancel-btn:hover {
    background: #e5e7eb;
}

.save-btn {
    background: #4f46e5;
    color: #ffffff;
}

.save-btn:hover:not(:disabled) {
    background: #4338ca;
}

.save-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 600px) {

    .groups-page {
        padding: 16px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .create-btn {
        width: 100%;
    }

    .groups-grid {
        grid-template-columns: 1fr;
    }

}

</style>