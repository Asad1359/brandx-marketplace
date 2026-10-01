<template>
    <Teleport to="body">
        <div class="modal-overlay" @click.self="$emit('close')">
            <div class="modal">

                <div class="modal-header">
                    <h3>Create New Group</h3>
                    <button @click="$emit('close')" class="close-btn">×</button>
                </div>

                <div class="modal-body">

                    <!-- Group Name -->
                    <div class="form-group">
                        <label>Group Name *</label>
                        <input
                            v-model="groupName"
                            type="text"
                            placeholder="e.g. Team Alpha"
                            maxlength="100"
                            @keyup.enter="createGroup"
                        />
                    </div>

                    <!-- Search Users -->
                    <div class="form-group">
                        <label>Add Members</label>

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search users by name or email..."
                            @input="searchUsers"
                        />

                        <!-- Loading -->
                        <div v-if="searchingUsers" class="searching">
                            Searching...
                        </div>

                        <!-- Results -->
                        <div v-else-if="availableUsers.length > 0" class="users-list">
                            <div
                                v-for="user in availableUsers"
                                :key="user.id"
                                class="user-item"
                                :class="{ selected: isSelected(user.id) }"
                                @click="toggleUser(user)"
                            >
                                <div class="user-avatar">
                                    {{ getInitials(user.name) }}
                                </div>
                                <div class="user-info">
                                    <strong>{{ user.name }}</strong>
                                    <span>{{ user.email }}</span>
                                </div>
                                <div class="checkmark" v-if="isSelected(user.id)">✓</div>
                            </div>
                        </div>

                        <!-- No results -->
                        <div v-else-if="searchQuery" class="no-results">
                            No users found
                        </div>
                    </div>

                    <!-- Selected Users -->
                    <div v-if="selectedUsers.length > 0" class="selected-users">
                        <label>Selected ({{ selectedUsers.length }})</label>
                        <div class="selected-chips">
                            <div
                                v-for="user in selectedUsers"
                                :key="user.id"
                                class="chip"
                            >
                                {{ user.name }}
                                <button @click="removeUser(user.id)">×</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button @click="$emit('close')" class="btn-cancel">
                        Cancel
                    </button>
                    <button
                        @click="createGroup"
                        class="btn-primary"
                        :disabled="!groupName.trim() || creating"
                    >
                        {{ creating ? 'Creating...' : 'Create Group' }}
                    </button>
                </div>

            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { createGroup as createGroupApi, addGroupMember, getConversations } from '../services/chat';
import api from '../services/chat';

const emit = defineEmits(['close', 'created']);

const groupName = ref('');
const searchQuery = ref('');
const searchingUsers = ref(false);
const availableUsers = ref([]);
const selectedUsers = ref([]);
const creating = ref(false);

let searchTimer = null;

const loadAllUsers = async () => {
    try {
        searchingUsers.value = true;
        const response = await api.get('/user/all');

        if (response.data?.success || Array.isArray(response.data)) {
            availableUsers.value = response.data.users || response.data || [];
        }
    } catch (error) {
        console.error('Failed to load users:', error);
    } finally {
        searchingUsers.value = false;
    }
};

const searchUsers = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        loadAllUsers();
    }, 400);
};

const isSelected = (userId) => {
    return selectedUsers.value.some((u) => u.id === userId);
};

const toggleUser = (user) => {
    const index = selectedUsers.value.findIndex((u) => u.id === user.id);
    if (index === -1) {
        selectedUsers.value.push(user);
    } else {
        selectedUsers.value.splice(index, 1);
    }
};

const removeUser = (userId) => {
    selectedUsers.value = selectedUsers.value.filter((u) => u.id !== userId);
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const createGroup = async () => {
    if (!groupName.value.trim() || creating.value) return;

    try {
        creating.value = true;

        // Step 1: Create group
        const response = await createGroupApi({
            name: groupName.value.trim(),
        });

        if (!response.data?.success) {
            throw new Error(response.data?.message || 'Failed to create group');
        }

        const group = response.data.group;

        // Step 2: Add selected members
        for (const user of selectedUsers.value) {
            try {
                await addGroupMember(group.id, user.id);
            } catch (err) {
                console.error(`Failed to add ${user.name}:`, err);
            }
        }

        emit('created', group);

    } catch (error) {
        alert(error.response?.data?.message || error.message || 'Failed to create group');
    } finally {
        creating.value = false;
    }
};

onMounted(() => {
    loadAllUsers();
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal {
    width: 100%;
    max-width: 500px;
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.close-btn {
    background: transparent;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #6b7280;
    line-height: 1;
}

.modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
}

.form-group input:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.searching, .no-results {
    text-align: center;
    padding: 12px;
    color: #6b7280;
    font-size: 13px;
}

.users-list {
    max-height: 240px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin-top: 8px;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}

.user-item:last-child {
    border-bottom: none;
}

.user-item:hover {
    background: #f8fafc;
}

.user-item.selected {
    background: #eef2ff;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.user-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-info strong {
    font-size: 13px;
    color: #111827;
}

.user-info span {
    font-size: 11px;
    color: #6b7280;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.checkmark {
    color: #4f46e5;
    font-weight: 700;
    font-size: 16px;
}

.selected-users {
    margin-top: 12px;
}

.selected-users label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.selected-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: #eef2ff;
    color: #4338ca;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.chip button {
    background: transparent;
    border: none;
    color: #4338ca;
    font-size: 16px;
    cursor: pointer;
    line-height: 1;
    padding: 0;
}

.modal-footer {
    display: flex;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
}

.btn-cancel, .btn-primary {
    flex: 1;
    height: 44px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}

.btn-cancel {
    background: #f3f4f6;
    color: #374151;
}

.btn-primary {
    background: #4f46e5;
    color: #ffffff;
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>