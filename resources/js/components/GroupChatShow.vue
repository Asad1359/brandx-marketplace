<template>
    <div class="group-chat-show">

        <!-- Header -->
        <div class="chat-header">
            <button class="back-btn" @click="$router.push('/chat/groups')">
                ← Back
            </button>

            <div class="group-info">
                <h3>{{ group?.name || 'Loading...' }}</h3>
                <span>{{ group?.members?.length || 0 }} members</span>
            </div>

            <button class="members-btn" @click="showMembers = true">
                👥
            </button>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="messages-container">
            <div v-if="loading" class="loading">Loading messages...</div>

            <div v-else-if="messages.length === 0" class="empty">
                <p>No messages yet. Be the first to say hello!</p>
            </div>

            <div
                v-else
                v-for="msg in messages"
                :key="msg.id"
                class="message-row"
                :class="{ mine: msg.sender_id === currentUserId }"
            >
                <div class="message-avatar" v-if="msg.sender_id !== currentUserId">
                    {{ getInitials(msg.sender?.name) }}
                </div>

                <div class="message-bubble">
                    <div class="sender-name" v-if="msg.sender_id !== currentUserId">
                        {{ msg.sender?.name || 'Unknown' }}
                    </div>
                    <p>{{ msg.message }}</p>
                    <small>{{ formatTime(msg.created_at) }}</small>
                </div>
            </div>
        </div>

        <!-- Input -->
        <form class="chat-input" @submit.prevent="sendMessage">
            <input
                v-model="newMessage"
                type="text"
                placeholder="Type a message..."
                :disabled="sending"
            />
            <button type="submit" :disabled="sending || !newMessage.trim()">
                {{ sending ? '...' : '➤' }}
            </button>
        </form>

        <!-- Members Modal -->
        <div v-if="showMembers" class="members-overlay" @click.self="showMembers = false">
            <div class="members-modal">
                <h3>Group Members ({{ group?.members?.length || 0 }})</h3>
                <div class="members-list">
                    <div
                        v-for="member in group?.members || []"
                        :key="member.id"
                        class="member-item"
                    >
                        <div class="member-avatar">
                            {{ getInitials(member.name) }}
                        </div>
                        <div class="member-info">
                            <strong>{{ member.name }}</strong>
                            <span>{{ member.email }}</span>
                        </div>
                        <span class="member-role">{{ member.pivot?.role }}</span>
                    </div>
                </div>
                <button @click="showMembers = false" class="close-members">Close</button>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { getGroupDetails, getGroupMessages, sendGroupMessage } from '../services/chat';
import api from '../services/chat';

const route = useRoute();
const groupId = route.params.id;

const group = ref(null);
const messages = ref([]);
const newMessage = ref('');
const loading = ref(false);
const sending = ref(false);
const currentUserId = ref(null);
const messagesContainer = ref(null);
const showMembers = ref(false);

let pollTimer = null;

const loadGroup = async () => {
    try {
        const response = await getGroupDetails(groupId);
        if (response.data?.success) {
            group.value = response.data.group;
        }
    } catch (error) {
        console.error('Failed to load group:', error);
    }
};

const loadMessages = async (showLoader = true) => {
    try {
        if (showLoader && messages.value.length === 0) loading.value = true;

        const response = await getGroupMessages(groupId);

        if (response.data?.success) {
            messages.value = response.data.messages || [];
            if (response.data.group) group.value = response.data.group;
            await scrollToBottom();
        }
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        loading.value = false;
    }
};

const loadCurrentUser = async () => {
    try {
        const response = await api.get('/user');
        currentUserId.value = response.data.id || response.data.user?.id;
    } catch (error) {
        console.error('Failed to load current user:', error);
    }
};

const sendMessage = async () => {
    const text = newMessage.value.trim();
    if (!text || sending.value) return;

    try {
        sending.value = true;
        const response = await sendGroupMessage(groupId, text);

        if (response.data?.success && response.data?.message) {
            messages.value.push(response.data.message);
            newMessage.value = '';
            await scrollToBottom();
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to send message');
    } finally {
        sending.value = false;
    }
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const getInitials = (name) => {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

onMounted(async () => {
    await loadCurrentUser();
    await loadGroup();
    await loadMessages();

    // Poll every 5 seconds
    pollTimer = setInterval(() => loadMessages(false), 5000);
});

onUnmounted(() => {
    if (pollTimer) clearInterval(pollTimer);
});
</script>

<style scoped>
.group-chat-show {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #f8fafc;
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.back-btn {
    background: transparent;
    border: none;
    font-size: 14px;
    color: #4f46e5;
    cursor: pointer;
    font-weight: 600;
}

.group-info {
    flex: 1;
    min-width: 0;
}

.group-info h3 {
    margin: 0 0 2px;
    font-size: 16px;
    color: #111827;
}

.group-info span {
    font-size: 12px;
    color: #6b7280;
}

.members-btn {
    background: #eef2ff;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
}

.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.loading, .empty {
    text-align: center;
    padding: 40px;
    color: #6b7280;
}

.message-row {
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.message-row.mine {
    flex-direction: row-reverse;
}

.message-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.message-bubble {
    max-width: 65%;
    padding: 8px 12px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    word-wrap: break-word;
}

.message-row.mine .message-bubble {
    background: #4f46e5;
    color: #ffffff;
    border: none;
}

.sender-name {
    font-size: 11px;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 4px;
}

.message-bubble p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
}

.message-bubble small {
    display: block;
    margin-top: 4px;
    font-size: 10px;
    opacity: 0.7;
}

.chat-input {
    display: flex;
    gap: 8px;
    padding: 12px 20px;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.chat-input input {
    flex: 1;
    height: 42px;
    padding: 0 16px;
    border: 1px solid #d1d5db;
    border-radius: 21px;
    outline: none;
    font-size: 14px;
}

.chat-input input:focus {
    border-color: #4f46e5;
}

.chat-input button {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 18px;
}

.chat-input button:disabled {
    opacity: 0.5;
}

.members-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.members-modal {
    background: #ffffff;
    border-radius: 14px;
    padding: 20px;
    width: 100%;
    max-width: 400px;
    max-height: 80vh;
    overflow-y: auto;
}

.members-modal h3 {
    margin: 0 0 16px;
    font-size: 16px;
}

.members-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    border-radius: 8px;
    background: #f8fafc;
}

.member-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

.member-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.member-info strong {
    font-size: 13px;
}

.member-info span {
    font-size: 11px;
    color: #6b7280;
}

.member-role {
    font-size: 10px;
    padding: 2px 8px;
    background: #eef2ff;
    color: #4338ca;
    border-radius: 10px;
    text-transform: uppercase;
    font-weight: 700;
}

.close-members {
    width: 100%;
    height: 42px;
    margin-top: 16px;
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}
</style>