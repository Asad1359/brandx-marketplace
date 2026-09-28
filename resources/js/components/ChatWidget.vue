<template>
    <div class="chat-widget">

        <!-- Floating Chat Button -->
        <button
            class="chat-button"
            @click="toggleChat"
            aria-label="Open chat"
        >
            <span v-if="!isOpen" class="chat-icon">
                💬
            </span>

            <span v-else class="close-icon">
                ×
            </span>

            <span
                v-if="unreadCount > 0 && !isOpen"
                class="unread-badge"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>


        <!-- Chat Window -->
        <div
            v-if="isOpen"
            class="chat-window"
        >

            <!-- Header -->
            <div class="chat-header">

                <div>
                    <h3>
                        Support Chat
                    </h3>

                    <span class="online-status">
                        ● Online
                    </span>
                </div>

                <button
                    class="header-close"
                    @click="closeChat"
                    type="button"
                >
                    ×
                </button>

            </div>


            <!-- Messages -->
            <div
                ref="messagesContainer"
                class="messages-container"
            >

                <!-- Loading -->
                <div
                    v-if="loading"
                    class="loading-message"
                >
                    Loading chat...
                </div>


                <!-- Empty -->
                <div
                    v-else-if="messages.length === 0"
                    class="empty-message"
                >
                    <div class="empty-icon">
                        💬
                    </div>

                    <p>
                        No messages yet.
                    </p>

                    <span>
                        Send a message to contact admin.
                    </span>
                </div>


                <!-- Messages List -->
                <template v-else>

                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="message-row"
                        :class="{
                            'user-message-row':
                                message.sender_type === 'user',

                            'admin-message-row':
                                message.sender_type === 'admin'
                        }"
                    >

                        <div
                            class="message-bubble"
                            :class="{
                                'user-message':
                                    message.sender_type === 'user',

                                'admin-message':
                                    message.sender_type === 'admin'
                            }"
                        >

                            <div class="message-text">
                                {{ message.message }}
                            </div>

                            <div class="message-time">
                                {{ formatTime(message.created_at) }}
                            </div>

                        </div>

                    </div>

                </template>

            </div>


            <!-- Input -->
            <form
                class="chat-input-area"
                @submit.prevent="sendMessage"
            >

                <input
                    v-model="newMessage"
                    type="text"
                    placeholder="Type a message..."
                    maxlength="5000"
                    :disabled="sending"
                    autocomplete="off"
                />

                <button
                    type="submit"
                    :disabled="sending || !newMessage.trim()"
                >
                    <span v-if="sending">
                        ...
                    </span>

                    <span v-else>
                        ➤
                    </span>
                </button>

            </form>

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
    getChat,
    getChatUnreadCount,
    sendChatMessage
} from '../services/chat';


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const isOpen = ref(false);

const loading = ref(false);

const sending = ref(false);

const messages = ref([]);

const newMessage = ref('');

const unreadCount = ref(0);

const messagesContainer = ref(null);

let unreadPollTimer = null;

let messagePollTimer = null;


/*
|--------------------------------------------------------------------------
| Load Chat
|--------------------------------------------------------------------------
*/

const loadChat = async () => {

    try {

        if (messages.value.length === 0) {
            loading.value = true;
        }

        const response = await getChat();

        if (response.data?.success) {

            messages.value =
                response.data.messages || [];

            await scrollToBottom();

        }

    } catch (error) {

        console.error(
            'Failed to load chat:',
            error.response?.data || error.message
        );

    } finally {

        loading.value = false;

    }

};


/*
|--------------------------------------------------------------------------
| Load Unread Count
|--------------------------------------------------------------------------
|
| Admin ke bheje hue unread messages ka count.
|
*/

const loadUnreadCount = async () => {

    try {

        const response =
            await getChatUnreadCount();

        if (response.data?.success) {

            const newCount =
                Number(response.data.count) || 0;

            /*
            |--------------------------------------------------------------------------
            | Agar count barha, to naya admin message aaya hai.
            | Chat khula ho to foran messages load karein.
            |--------------------------------------------------------------------------
            */

            if (
                newCount > unreadCount.value &&
                isOpen.value
            ) {
                await loadChat();
                unreadCount.value = 0;

                return;
            }

            unreadCount.value = newCount;

        }

    } catch (error) {

        console.error(
            'Failed to load unread count:',
            error.response?.data || error.message
        );

    }

};


/*
|--------------------------------------------------------------------------
| Toggle Chat
|--------------------------------------------------------------------------
*/

const toggleChat = async () => {

    if (isOpen.value) {
        closeChat();
        return;
    }

    isOpen.value = true;

    await loadChat();

    /*
    |--------------------------------------------------------------------------
    | Chat open hone ke baad unread badge remove
    |--------------------------------------------------------------------------
    */

    unreadCount.value = 0;

};


/*
|--------------------------------------------------------------------------
| Close Chat
|--------------------------------------------------------------------------
*/

const closeChat = () => {
    isOpen.value = false;
};


/*
|--------------------------------------------------------------------------
| Send Message
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {

    const messageText =
        newMessage.value.trim();

    if (!messageText) {
        return;
    }

    if (sending.value) {
        return;
    }

    try {

        sending.value = true;

        const response =
            await sendChatMessage(messageText);

        if (
            response.data?.success &&
            response.data?.message
        ) {

            messages.value.push(
                response.data.message
            );

            newMessage.value = '';

            await scrollToBottom();

        }

    } catch (error) {

        console.error(
            'Failed to send message:',
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
| Polling
|--------------------------------------------------------------------------
|
| 1. Unread count har 5 seconds pe check hoti hai.
| 2. Chat khula ho to messages bhi har 5 seconds pe refresh.
|
*/

const startPolling = () => {

    stopPolling();

    /*
    |--------------------------------------------------------------------------
    | Unread count — har 5s
    |--------------------------------------------------------------------------
    */

    unreadPollTimer = setInterval(
        async () => {

            await loadUnreadCount();

        },
        5000
    );


    /*
    |--------------------------------------------------------------------------
    | Messages — sirf jab chat khula ho, har 5s
    |--------------------------------------------------------------------------
    */

    messagePollTimer = setInterval(
        async () => {

            if (isOpen.value) {
                await loadChat();
            }

        },
        5000
    );

};


/*
|--------------------------------------------------------------------------
| Stop Polling
|--------------------------------------------------------------------------
*/

const stopPolling = () => {

    if (unreadPollTimer) {
        clearInterval(unreadPollTimer);
        unreadPollTimer = null;
    }

    if (messagePollTimer) {
        clearInterval(messagePollTimer);
        messagePollTimer = null;
    }

};


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {

    /*
    |--------------------------------------------------------------------------
    | Pehli dafa unread count check karein
    |--------------------------------------------------------------------------
    */

    await loadUnreadCount();

    /*
    |--------------------------------------------------------------------------
    | Polling shuru karein
    |--------------------------------------------------------------------------
    */

    startPolling();

});


onUnmounted(() => {

    stopPolling();

});

</script>


<style scoped>

.chat-widget {
    position: fixed;

    right: 24px;
    bottom: 24px;

    z-index: 9999;

    font-family:
        Arial,
        sans-serif;
}


/* -------------------------------------------------
   Chat Button
------------------------------------------------- */

.chat-button {
    position: relative;

    width: 58px;
    height: 58px;

    border: none;

    border-radius: 50%;

    background: #4f46e5;

    color: white;

    font-size: 25px;

    cursor: pointer;

    box-shadow:
        0 6px 20px rgba(0, 0, 0, 0.25);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}

.chat-button:hover {

    transform: scale(1.06);

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.30);

}

.chat-icon {
    font-size: 25px;
}

.close-icon {
    font-size: 32px;

    line-height: 1;
}


/* -------------------------------------------------
   Unread Badge
------------------------------------------------- */

.unread-badge {

    position: absolute;

    top: -4px;
    right: -4px;

    min-width: 22px;
    height: 22px;

    padding: 0 6px;

    display: flex;

    align-items: center;
    justify-content: center;

    background: #ef4444;

    color: white;

    border-radius: 50%;

    font-size: 11px;

    font-weight: bold;

    border: 2px solid white;

    animation: pulse 1.5s infinite;
}


/* -------------------------------------------------
   Badge Pulse Animation
------------------------------------------------- */

@keyframes pulse {

    0% {
        box-shadow:
            0 0 0 0
            rgba(239, 68, 68, 0.7);
    }

    70% {
        box-shadow:
            0 0 0 10px
            rgba(239, 68, 68, 0);
    }

    100% {
        box-shadow:
            0 0 0 0
            rgba(239, 68, 68, 0);
    }

}


/* -------------------------------------------------
   Chat Window
------------------------------------------------- */

.chat-window {

    position: absolute;

    right: 0;
    bottom: 70px;

    width: 360px;
    height: 500px;

    background: white;

    border-radius: 14px;

    overflow: hidden;

    display: flex;

    flex-direction: column;

    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.25);

    border: 1px solid #e5e7eb;
}


/* -------------------------------------------------
   Header
------------------------------------------------- */

.chat-header {

    height: 68px;

    padding: 0 16px;

    background: #4f46e5;

    color: white;

    display: flex;

    align-items: center;

    justify-content: space-between;

    flex-shrink: 0;
}

.chat-header h3 {

    margin: 0 0 4px;

    font-size: 17px;
}

.online-status {

    font-size: 12px;

    opacity: 0.9;
}

.header-close {

    border: none;

    background: transparent;

    color: white;

    font-size: 28px;

    cursor: pointer;

    padding: 5px 8px;
}


/* -------------------------------------------------
   Messages
------------------------------------------------- */

.messages-container {

    flex: 1;

    overflow-y: auto;

    padding: 16px;

    background: #f8fafc;

    scroll-behavior: smooth;
}

.message-row {

    display: flex;

    margin-bottom: 10px;
}

.user-message-row {

    justify-content: flex-end;
}

.admin-message-row {

    justify-content: flex-start;
}

.message-bubble {

    max-width: 78%;

    padding: 10px 12px;

    border-radius: 12px;

    word-break: break-word;
}

.user-message {

    background: #4f46e5;

    color: white;

    border-bottom-right-radius: 4px;
}

.admin-message {

    background: white;

    color: #1f2937;

    border: 1px solid #e5e7eb;

    border-bottom-left-radius: 4px;
}

.message-text {

    font-size: 14px;

    line-height: 1.45;

    white-space: pre-wrap;
}

.message-time {

    margin-top: 5px;

    font-size: 10px;

    opacity: 0.65;

    text-align: right;
}


/* -------------------------------------------------
   Loading
------------------------------------------------- */

.loading-message {

    text-align: center;

    color: #6b7280;

    padding: 30px 10px;

    font-size: 14px;
}


/* -------------------------------------------------
   Empty Chat
------------------------------------------------- */

.empty-message {

    height: 100%;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    text-align: center;

    color: #6b7280;
}

.empty-icon {

    font-size: 40px;

    margin-bottom: 10px;
}

.empty-message p {

    margin: 0 0 5px;

    font-weight: 600;

    color: #374151;
}

.empty-message span {

    font-size: 13px;
}


/* -------------------------------------------------
   Input
------------------------------------------------- */

.chat-input-area {

    display: flex;

    align-items: center;

    gap: 8px;

    padding: 10px;

    background: white;

    border-top: 1px solid #e5e7eb;

    flex-shrink: 0;
}

.chat-input-area input {

    flex: 1;

    height: 40px;

    border: 1px solid #d1d5db;

    border-radius: 20px;

    padding: 0 14px;

    outline: none;

    font-size: 14px;

    min-width: 0;
}

.chat-input-area input:focus {

    border-color: #4f46e5;

    box-shadow:
        0 0 0 2px rgba(79, 70, 229, 0.10);
}

.chat-input-area button {

    width: 40px;
    height: 40px;

    border: none;

    border-radius: 50%;

    background: #4f46e5;

    color: white;

    cursor: pointer;

    font-size: 18px;

    flex-shrink: 0;
}

.chat-input-area button:hover:not(:disabled) {

    background: #4338ca;
}

.chat-input-area button:disabled {

    opacity: 0.5;

    cursor: not-allowed;
}


/* -------------------------------------------------
   Scrollbar
------------------------------------------------- */

.messages-container::-webkit-scrollbar {

    width: 6px;
}

.messages-container::-webkit-scrollbar-thumb {

    background: #cbd5e1;

    border-radius: 10px;
}


/* -------------------------------------------------
   Mobile
------------------------------------------------- */

@media (max-width: 480px) {

    .chat-widget {

        right: 15px;

        bottom: 15px;
    }

    .chat-window {

        position: fixed;

        left: 10px;

        right: 10px;

        bottom: 82px;

        width: auto;

        height: calc(100vh - 120px);

        max-height: 600px;
    }

    .chat-button {

        width: 54px;

        height: 54px;
    }

}

</style>