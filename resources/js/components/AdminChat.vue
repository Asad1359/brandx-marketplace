<template>
    <div class="admin-chat">

        <!-- LEFT: Chat Users -->
        <div class="chat-users">

            <div class="users-header">

                <div>
                    <h2>Customer Chats</h2>
                    <p>Users ke messages</p>
                </div>

                <button
                    class="refresh-button"
                    @click="loadConversations"
                    :disabled="loadingConversations"
                    type="button"
                >
                    ↻
                </button>

            </div>


            <!-- Loading -->
            <div
                v-if="loadingConversations"
                class="loading-users"
            >
                Loading chats...
            </div>


            <!-- No Chats -->
            <div
                v-else-if="conversations.length === 0"
                class="no-chats"
            >

                <div class="no-chat-icon">
                    💬
                </div>

                <p>
                    No conversations yet.
                </p>

            </div>


            <!-- User List -->
            <div
                v-else
                class="user-list"
            >

                <button
                    v-for="conversation in conversations"
                    :key="conversation.id"
                    class="user-item"
                    :class="{
                        active:
                            selectedUserId ===
                            conversation.user_id
                    }"
                    @click="selectConversation(conversation)"
                    type="button"
                >

                    <div class="user-avatar">
                        {{ getInitials(conversation.user) }}
                    </div>


                    <div class="user-info">

                        <div class="user-name-row">

                            <strong>
                                {{
                                    conversation.user?.name ||
                                    'Unknown User'
                                }}
                            </strong>


                            <span
                                v-if="
                                    Number(
                                        conversation.unread_admin
                                    ) > 0
                                "
                                class="unread-count"
                            >
                                {{
                                    conversation.unread_admin
                                }}
                            </span>

                        </div>


                        <p>
                            {{
                                conversation.last_message ||
                                'No messages yet'
                            }}
                        </p>

                    </div>

                </button>

            </div>

        </div>


        <!-- RIGHT: Conversation -->
        <div class="conversation-panel">


            <!-- No Selected User -->
            <div
                v-if="!selectedUser"
                class="no-selected-user"
            >

                <div class="big-chat-icon">
                    💬
                </div>

                <h3>
                    Select a conversation
                </h3>

                <p>
                    Left side se kisi user ko select karein.
                </p>

            </div>


            <!-- Selected Conversation -->
            <template v-else>


                <!-- Conversation Header -->
                <div class="conversation-header">

                    <div class="selected-user-avatar">
                        {{ getInitials(selectedUser) }}
                    </div>

                    <div>

                        <h3>
                            {{ selectedUser.name }}
                        </h3>

                        <span>
                            {{ selectedUser.email }}
                        </span>

                    </div>

                </div>


                <!-- Messages -->
                <div
                    ref="messagesContainer"
                    class="messages-container"
                >

                    <!-- Loading -->
                    <div
                        v-if="loadingMessages"
                        class="loading-messages"
                    >
                        Loading messages...
                    </div>


                    <!-- Empty -->
                    <div
                        v-else-if="messages.length === 0"
                        class="empty-messages"
                    >
                        No messages in this conversation.
                    </div>


                    <!-- Messages -->
                    <template v-else>

                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="message-row"
                            :class="{
                                'admin-row':
                                    message.sender_type === 'admin',

                                'user-row':
                                    message.sender_type === 'user'
                            }"
                        >

                            <div
                                class="message-bubble"
                                :class="{
                                    'admin-message':
                                        message.sender_type === 'admin',

                                    'user-message':
                                        message.sender_type === 'user'
                                }"
                            >

                                <div class="message-sender">

                                    {{
                                        message.sender_type ===
                                        'admin'
                                            ? 'You'
                                            : selectedUser.name
                                    }}

                                </div>


                                <div class="message-text">
                                    {{ message.message }}
                                </div>


                                <div class="message-time">
                                    {{
                                        formatTime(
                                            message.created_at
                                        )
                                    }}
                                </div>

                            </div>

                        </div>

                    </template>

                </div>


                <!-- Reply Box -->
                <form
                    class="reply-area"
                    @submit.prevent="sendMessage"
                >

                    <input
                        v-model="newMessage"
                        type="text"
                        maxlength="5000"
                        placeholder="Type your reply..."
                        :disabled="sending"
                        autocomplete="off"
                    />


                    <button
                        type="submit"
                        :disabled="
                            sending ||
                            !newMessage.trim()
                        "
                    >

                        <span v-if="sending">
                            ...
                        </span>

                        <span v-else>
                            Send
                        </span>

                    </button>

                </form>

            </template>

        </div>

    </div>
</template>


<script setup>

import {
    ref,
    onMounted,
    onUnmounted,
    nextTick
} from 'vue';

import {
    getAdminChats,
    getAdminConversation,
    sendAdminChatMessage
} from '../services/chat';

import { useAdminChatChannel } from '../composables/useChatChannel';


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const conversations = ref([]);

const selectedUser = ref(null);

const selectedUserId = ref(null);

const messages = ref([]);

const newMessage = ref('');

const loadingConversations = ref(false);

const loadingMessages = ref(false);

const sending = ref(false);

const messagesContainer = ref(null);

let pollingTimer = null;


/*
|--------------------------------------------------------------------------
| Websocket channel
|--------------------------------------------------------------------------
*/

const { connected } = useAdminChatChannel({

    onMessage: (payload) => {

        /*
         * If this message is in the currently selected
         * conversation, append it.
         */
        if (
            Number(payload.user_id) ===
            Number(selectedUserId.value)
        ) {

            if (
                !messages.value.some(
                    (m) => m.id === payload.id
                )
            ) {
                messages.value.push(payload);

                scrollToBottom();
            }
        }

        /*
         * Refresh sidebar so unread counts and
         * previews update.
         */
        loadConversations();
    },

    onNewConversation: () => {

        loadConversations();
    },
});


/*
|--------------------------------------------------------------------------
| Load All Conversations
|--------------------------------------------------------------------------
*/

const loadConversations = async () => {

    try {

        loadingConversations.value = true;

        const response =
            await getAdminChats();

        if (response.data?.success) {

            conversations.value =
                response.data.chats || [];

        }

    } catch (error) {

        console.error(
            'Failed to load conversations:',
            error.response?.data || error.message
        );

    } finally {

        loadingConversations.value = false;

    }

};


/*
|--------------------------------------------------------------------------
| Select Conversation
|--------------------------------------------------------------------------
*/

const selectConversation = async (conversation) => {

    const userId =
        conversation.user_id;

    selectedUserId.value =
        userId;

    selectedUser.value =
        conversation.user || null;

    await loadConversation(userId);

};


/*
|--------------------------------------------------------------------------
| Load Selected Conversation
|--------------------------------------------------------------------------
*/

const loadConversation = async (
    userId,
    showLoading = true
) => {

    try {

        if (showLoading) {

            loadingMessages.value = true;

        }

        const response =
            await getAdminConversation(userId);

        if (response.data?.success) {

            messages.value =
                response.data.messages || [];

            if (!selectedUser.value) {

                const conversation =
                    conversations.value.find(
                        item =>
                            Number(item.user_id) ===
                            Number(userId)
                    );

                selectedUser.value =
                    conversation?.user || null;

            }

            await scrollToBottom();

        }

    } catch (error) {

        console.error(
            'Failed to load conversation:',
            error.response?.data || error.message
        );

    } finally {

        if (showLoading) {

            loadingMessages.value = false;

        }

    }

};


/*
|--------------------------------------------------------------------------
| Send Admin Message
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {

    const messageText =
        newMessage.value.trim();

    if (!messageText) {
        return;
    }

    if (!selectedUserId.value) {
        return;
    }

    if (sending.value) {
        return;
    }

    try {

        sending.value = true;

        const response =
            await sendAdminChatMessage(
                selectedUserId.value,
                messageText
            );

        if (
            response.data?.success &&
            response.data?.message
        ) {

            messages.value.push(
                response.data.message
            );

            newMessage.value = '';

            await scrollToBottom();

            await loadConversations();

        }

    } catch (error) {

        console.error(
            'Failed to send admin message:',
            error.response?.data || error.message
        );

        const errorMessage =
            error.response?.data?.message ||
            error.response?.data?.errors?.message?.[0] ||
            'Message send nahi ho saka.';

        alert(errorMessage);

    } finally {

        sending.value = false;

    }

};


/*
|--------------------------------------------------------------------------
| Scroll To Bottom
|--------------------------------------------------------------------------
*/

const scrollToBottom = async () => {

    await nextTick();

    if (messagesContainer.value) {

        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;

    }

};


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

const getInitials = (user) => {

    if (!user?.name) {

        return '?';

    }

    const parts =
        user.name
            .trim()
            .split(/\s+/);

    if (parts.length === 1) {

        return parts[0]
            .substring(0, 2)
            .toUpperCase();

    }

    return (
        parts[0][0] +
        parts[parts.length - 1][0]
    ).toUpperCase();

};


/*
|--------------------------------------------------------------------------
| Format Time
|--------------------------------------------------------------------------
*/

const formatTime = (date) => {

    if (!date) {

        return '';

    }

    const messageDate =
        new Date(date);

    if (
        Number.isNaN(
            messageDate.getTime()
        )
    ) {

        return '';

    }

    return messageDate.toLocaleTimeString(
        [],
        {
            hour: '2-digit',
            minute: '2-digit'
        }
    );

};


/*
|--------------------------------------------------------------------------
| Fallback Polling
|--------------------------------------------------------------------------
|
| Only starts if the websocket did not connect.
| Polls every 15 seconds instead of every 3.
|
*/

const startFallbackPolling = () => {

    stopPolling();

    pollingTimer =
        setInterval(async () => {

            await loadConversations();

            if (selectedUserId.value) {

                await loadConversation(
                    selectedUserId.value,
                    false
                );

            }

        }, 15000);

};


/*
|--------------------------------------------------------------------------
| Stop Polling
|--------------------------------------------------------------------------
*/

const stopPolling = () => {

    if (pollingTimer) {

        clearInterval(pollingTimer);

        pollingTimer = null;

    }

};


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {

    await loadConversations();

    setTimeout(() => {

        if (!connected.value) {
            startFallbackPolling();
        }

    }, 1500);

});


onUnmounted(() => {

    stopPolling();

});

</script>


<style scoped>

.admin-chat {

    width: 100%;

    height: 650px;

    display: grid;

    grid-template-columns: 320px 1fr;

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 4px 20px rgba(0, 0, 0, 0.08);
}


/* -------------------------------------------------
   LEFT USERS
------------------------------------------------- */

.chat-users {

    display: flex;

    flex-direction: column;

    border-right: 1px solid #e5e7eb;

    min-width: 0;
}


.users-header {

    height: 75px;

    padding: 12px 15px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    border-bottom: 1px solid #e5e7eb;
}


.users-header h2 {

    margin: 0 0 4px;

    font-size: 17px;

    color: #111827;
}


.users-header p {

    margin: 0;

    font-size: 12px;

    color: #6b7280;
}


.refresh-button {

    width: 36px;

    height: 36px;

    border: 1px solid #d1d5db;

    border-radius: 8px;

    background: white;

    font-size: 20px;

    cursor: pointer;
}


.refresh-button:hover {

    background: #f3f4f6;

}


/* -------------------------------------------------
   User List
------------------------------------------------- */

.user-list {

    flex: 1;

    overflow-y: auto;
}


.user-item {

    width: 100%;

    display: flex;

    align-items: center;

    gap: 11px;

    padding: 12px;

    border: none;

    border-bottom: 1px solid #f1f5f9;

    background: white;

    text-align: left;

    cursor: pointer;
}


.user-item:hover {

    background: #f8fafc;

}


.user-item.active {

    background: #eef2ff;

}


/* -------------------------------------------------
   Avatar
------------------------------------------------- */

.user-avatar,
.selected-user-avatar {

    flex-shrink: 0;

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #4f46e5;

    color: white;

    font-size: 13px;

    font-weight: 700;

}


/* -------------------------------------------------
   User Info
------------------------------------------------- */

.user-info {

    min-width: 0;

    flex: 1;
}


.user-name-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 8px;
}


.user-name-row strong {

    color: #111827;

    font-size: 14px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.user-info p {

    margin: 5px 0 0;

    color: #6b7280;

    font-size: 12px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}


.unread-count {

    min-width: 20px;

    height: 20px;

    padding: 0 5px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #ef4444;

    color: white;

    font-size: 10px;

    font-weight: 700;

}


/* -------------------------------------------------
   Right Panel
------------------------------------------------- */

.conversation-panel {

    min-width: 0;

    display: flex;

    flex-direction: column;

    background: #f8fafc;
}


/* -------------------------------------------------
   Header
------------------------------------------------- */

.conversation-header {

    height: 75px;

    padding: 0 18px;

    display: flex;

    align-items: center;

    gap: 12px;

    background: white;

    border-bottom: 1px solid #e5e7eb;
}


.conversation-header h3 {

    margin: 0 0 4px;

    font-size: 16px;

    color: #111827;
}


.conversation-header span {

    font-size: 12px;

    color: #6b7280;
}


/* -------------------------------------------------
   Messages
------------------------------------------------- */

.messages-container {

    flex: 1;

    overflow-y: auto;

    padding: 20px;

    scroll-behavior: smooth;
}


.message-row {

    display: flex;

    margin-bottom: 12px;
}


.user-row {

    justify-content: flex-start;
}


.admin-row {

    justify-content: flex-end;
}


.message-bubble {

    max-width: 70%;

    padding: 10px 13px;

    border-radius: 12px;

    word-break: break-word;
}


.user-message {

    background: white;

    color: #1f2937;

    border: 1px solid #e5e7eb;

    border-bottom-left-radius: 4px;
}


.admin-message {

    background: #4f46e5;

    color: white;

    border-bottom-right-radius: 4px;
}


.message-sender {

    font-size: 10px;

    font-weight: 700;

    margin-bottom: 4px;

    opacity: 0.7;
}


.message-text {

    font-size: 14px;

    line-height: 1.5;

    white-space: pre-wrap;
}


.message-time {

    margin-top: 5px;

    text-align: right;

    font-size: 10px;

    opacity: 0.65;
}


/* -------------------------------------------------
   Reply
------------------------------------------------- */

.reply-area {

    display: flex;

    gap: 10px;

    padding: 12px;

    background: white;

    border-top: 1px solid #e5e7eb;
}


.reply-area input {

    flex: 1;

    height: 42px;

    padding: 0 14px;

    border: 1px solid #d1d5db;

    border-radius: 21px;

    outline: none;

    font-size: 14px;

    min-width: 0;
}


.reply-area input:focus {

    border-color: #4f46e5;

}


.reply-area button {

    min-width: 70px;

    height: 42px;

    padding: 0 15px;

    border: none;

    border-radius: 21px;

    background: #4f46e5;

    color: white;

    cursor: pointer;

    font-weight: 600;
}


.reply-area button:hover:not(:disabled) {

    background: #4338ca;

}


.reply-area button:disabled {

    opacity: 0.5;

    cursor: not-allowed;

}


/* -------------------------------------------------
   Empty States
------------------------------------------------- */

.no-selected-user,
.no-chats,
.empty-messages,
.loading-users,
.loading-messages {

    display: flex;

    align-items: center;

    justify-content: center;

    text-align: center;

    color: #6b7280;
}


.no-selected-user {

    flex: 1;

    flex-direction: column;
}


.big-chat-icon {

    font-size: 55px;

    margin-bottom: 12px;
}


.no-selected-user h3 {

    margin: 0 0 5px;

    color: #374151;
}


.no-selected-user p {

    margin: 0;

    font-size: 13px;
}


.no-chats {

    flex: 1;

    flex-direction: column;
}


.no-chat-icon {

    font-size: 40px;

    margin-bottom: 10px;
}


.no-chats p {

    margin: 0;

    font-size: 13px;
}


.loading-users {

    flex: 1;

}


.loading-messages {

    height: 100%;

}


.empty-messages {

    height: 100%;

}


/* -------------------------------------------------
   Scrollbars
------------------------------------------------- */

.user-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar {

    width: 6px;
}


.user-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb {

    background: #cbd5e1;

    border-radius: 10px;
}


/* -------------------------------------------------
   Responsive
------------------------------------------------- */

@media (max-width: 900px) {

    .admin-chat {

        grid-template-columns: 260px 1fr;

    }

    .message-bubble {

        max-width: 80%;

    }

}


@media (max-width: 650px) {

    .admin-chat {

        height: 700px;

        grid-template-columns: 1fr;

    }

    .chat-users {

        max-height: 220px;

        border-right: none;

        border-bottom: 1px solid #e5e7eb;

    }

    .conversation-panel {

        min-height: 480px;

    }

}

</style>