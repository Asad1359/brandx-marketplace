<template>
    <div class="group-chat-list">

        <!-- Header -->
        <div class="header">
            <h2>My Groups</h2>

            <button class="create-btn" @click="showCreateModal = true">
                + New Group
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading">
            Loading groups...
        </div>

        <!-- Empty -->
        <div v-else-if="groups.length === 0" class="empty">
            <div class="empty-icon">👥</div>
            <p>No groups yet.</p>
            <button class="create-btn" @click="showCreateModal = true">
                Create your first group
            </button>
        </div>

        <!-- List -->
        <div v-else class="group-list">
            <div
                v-for="group in groups"
                :key="group.id"
                class="group-item"
                @click="openGroup(group)"
            >
                <div class="group-avatar">
                    {{ getInitials(group.name) }}
                </div>

                <div class="group-info">
                    <h3>{{ group.name }}</h3>
                    <p>
                        {{ group.members_count }} members
                        <span v-if="group.last_message">
                            • {{ group.last_message }}
                        </span>
                    </p>
                </div>

                <div class="arrow">→</div>
            </div>
        </div>

        <!-- Create Group Modal -->
        <CreateGroupModal
            v-if="showCreateModal"
            @close="showCreateModal = false"
            @created="onGroupCreated"
        />

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { getUserGroups } from '../services/chat';
import CreateGroupModal from './CreateGroupModal.vue';

const router = useRouter();

const groups = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);

const loadGroups = async () => {
    try {
        loading.value = true;
        const response = await getUserGroups();

        if (response.data?.success) {
            groups.value = response.data.groups || [];
        }
    } catch (error) {
        console.error('Failed to load groups:', error);
    } finally {
        loading.value = false;
    }
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const openGroup = (group) => {
    router.push(`/chat/groups/${group.id}`);
};

const onGroupCreated = (newGroup) => {
    showCreateModal.value = false;
    router.push(`/chat/groups/${newGroup.id}`);
};

onMounted(() => {
    loadGroups();
});
</script>

<style scoped>
.group-chat-list {
    max-width: 700px;
    margin: 0 auto;
    padding: 20px;
    height: 100vh;
    overflow-y: auto;
    background: #f8fafc;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.header h2 {
    margin: 0;
    font-size: 24px;
    color: #111827;
}

.create-btn {
    background: #4f46e5;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.15s;
}

.create-btn:hover {
    background: #4338ca;
}

.loading, .empty {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.empty-icon {
    font-size: 60px;
    margin-bottom: 16px;
}

.empty p {
    margin: 0 0 20px;
    font-size: 16px;
}

.group-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.group-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: #ffffff;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid #e5e7eb;
}

.group-item:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
    transform: translateY(-1px);
}

.group-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

.group-info {
    flex: 1;
    min-width: 0;
}

.group-info h3 {
    margin: 0 0 4px;
    font-size: 15px;
    color: #111827;
}

.group-info p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.arrow {
    color: #9ca3af;
    font-size: 20px;
    flex-shrink: 0;
}
</style>