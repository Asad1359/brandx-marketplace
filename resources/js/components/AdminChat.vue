<template>
    <div class="admin-chat">

        <!-- =========================================================
             LEFT: Chat Users
        ========================================================== -->
        <div class="chat-users">

            <div class="users-header">
                <div>
                    <h2>Customer Chats</h2>
                    <p>{{ connected ? '● Live' : 'Users ke messages' }}</p>
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

            <div v-if="loadingConversations" class="loading-users">
                Loading chats...
            </div>

            <div
                v-else-if="conversations.length === 0"
                class="no-chats"
            >
                <div class="no-chat-icon">💬</div>
                <p>No conversations yet.</p>
            </div>

            <div v-else class="user-list">
                <button
                    v-for="conversation in conversations"
                    :key="conversation.id"
                    class="user-item"
                    :class="{
                        active: selectedUserId === conversation.user_id
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
                                {{ conversation.user?.name || 'Unknown User' }}
                            </strong>

                            <span
                                v-if="Number(conversation.unread_admin) > 0"
                                class="unread-count"
                            >
                                {{ conversation.unread_admin }}
                            </span>
                        </div>

                        <p>{{ conversation.last_message || 'No messages yet' }}</p>
                    </div>
                </button>
            </div>

        </div>


        <!-- =========================================================
             RIGHT: Conversation
        ========================================================== -->
        <div class="conversation-panel">

            <div v-if="!selectedUser" class="no-selected-user">
                <div class="big-chat-icon">💬</div>
                <h3>Select a conversation</h3>
                <p>Left side se kisi user ko select karein.</p>
            </div>

            <template v-else>

                <div class="conversation-header">
                    <div class="selected-user-avatar">
                        {{ getInitials(selectedUser) }}
                    </div>

                    <div>
                        <h3>{{ selectedUser.name }}</h3>
                        <span>{{ selectedUser.email }}</span>
                    </div>
                </div>

                <div
                    ref="messagesContainer"
                    class="messages-container"
                >
                    <div v-if="loadingMessages" class="loading-messages">
                        Loading messages...
                    </div>

                    <div
                        v-else-if="messages.length === 0"
                        class="empty-messages"
                    >
                        No messages in this conversation.
                    </div>

                    <template v-else>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="message-row"
                            :class="{
                                'admin-row': message.sender_type === 'admin',
                                'user-row': message.sender_type === 'user'
                            }"
                        >
                            <div
                                class="message-bubble"
                                :class="{
                                    'admin-message': message.sender_type === 'admin',
                                    'user-message': message.sender_type === 'user'
                                }"
                            >
                                <div class="message-sender">
                                    {{
                                        message.sender_type === 'admin'
                                            ? 'You'
                                            : selectedUser.name
                                    }}
                                </div>

                                <!-- Attachment -->
                                <div
                                    v-if="message.attachment_url"
                                    class="message-attachment"
                                >

                                    <!-- Image -->
                                    <img
                                        v-if="message.is_image"
                                        :src="message.attachment_url"
                                        :alt="message.attachment_name"
                                        class="attachment-image"
                                        @click="openAttachment(message.attachment_url)"
                                    />

                                    <!-- Video -->
                                    <video
                                        v-else-if="message.is_video"
                                        :src="message.attachment_url"
                                        controls
                                        class="attachment-video"
                                    ></video>

                                    <!-- Audio (WhatsApp-style player) -->
                                    <VoicePlayer
                                        v-else-if="message.is_audio"
                                        :src="message.attachment_url"
                                        :own="message.sender_type === 'admin'"
                                    />

                                    <!-- PDF/Doc/etc. -->
                                    <a
                                        v-else
                                        :href="message.attachment_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="attachment-file"
                                    >
                                        <span class="file-icon">📎</span>
                                        <span class="file-name">
                                            {{ message.attachment_name }}
                                        </span>
                                        <span class="file-size">
                                            {{ formatSize(message.attachment_size) }}
                                        </span>
                                    </a>
                                </div>

                                <!-- Text -->
                                <div
                                    v-if="message.message"
                                    class="message-text"
                                >
                                    {{ message.message }}
                                </div>

                                <div class="message-time">
                                    {{ formatTime(message.created_at) }}
                                </div>
                            </div>
                        </div>
                    </template>
                </div>


                <!-- Selected File Preview -->
                <div v-if="selectedFile" class="file-preview">
                    <span class="file-icon">📎</span>

                    <span class="file-name">
                        {{ selectedFile.name }}
                    </span>

                    <span class="file-size">
                        {{ formatSize(selectedFile.size) }}
                    </span>

                    <button
                        class="remove-file"
                        type="button"
                        @click="clearSelectedFile"
                        :disabled="sending"
                        aria-label="Remove file"
                    >
                        ×
                    </button>
                </div>


                <!-- Reply Area -->
                <form
                    class="reply-area"
                    @submit.prevent="sendMessage"
                >

                    <!-- Hidden file input -->
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                        style="display: none;"
                        @change="onFileSelected"
                    />

                    <!-- Attach button -->
                    <button
                        v-if="!isRecording"
                        type="button"
                        class="attach-button"
                        @click="openFilePicker"
                        :disabled="sending"
                        title="Attach file"
                        aria-label="Attach file"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>

                    <!-- Mic button -->
                    <button
                        v-if="!isRecording && !selectedFile"
                        type="button"
                        class="mic-button"
                        @click="startRecording"
                        :disabled="sending || !!newMessage.trim()"
                        title="Record voice message"
                        aria-label="Record voice message"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="22"
                            height="22"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                            <line x1="8" y1="23" x2="16" y2="23"/>
                        </svg>
                    </button>

                    <!-- Text input -->
                    <input
                        v-if="!isRecording"
                        v-model="newMessage"
                        type="text"
                        maxlength="5000"
                        placeholder="Type your reply..."
                        :disabled="sending"
                        autocomplete="off"
                    />

                    <!-- Send button -->
                    <button
                        v-if="!isRecording"
                        type="submit"
                        class="send-button"
                        :disabled="
                            sending ||
                            (!newMessage.trim() && !selectedFile)
                        "
                    >
                        <span v-if="sending">...</span>
                        <span v-else>Send</span>
                    </button>


                    <!-- =========================================
                         Recording Bar (WhatsApp-style)
                    ========================================== -->
                    <div v-if="isRecording" class="recording-bar">

                        <!-- Cancel button -->
                        <button
                            type="button"
                            class="recording-cancel"
                            @click="cancelRecording"
                            aria-label="Cancel recording"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="14"
                                height="14"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            >
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </button>

                        <!-- Red pulsing dot -->
                        <span class="recording-dot"></span>

                        <!-- Timer -->
                        <span class="recording-time">
                            {{ formattedRecordTime }}
                        </span>

                        <!-- Live waveform -->
                        <div class="recording-waveform">
                            <span
                                v-for="(height, i) in liveBars"
                                :key="i"
                                class="rec-bar"
                                :style="{ height: height + '%' }"
                            ></span>
                        </div>

                        <!-- Send button -->
                        <button
                            type="button"
                            class="recording-stop"
                            @click="stopAndSendRecording"
                            :disabled="recordSeconds < 1"
                            aria-label="Send recording"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path d="M2 21l21-9L2 3v7l15 2-15 2z"/>
                            </svg>
                        </button>

                    </div>

                </form>

            </template>

        </div>

    </div>
</template>


<script setup>
import {
    ref,
    computed,
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

import VoicePlayer from './VoicePlayer.vue';


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
const fileInput = ref(null);
const selectedFile = ref(null);

let pollingTimer = null;


/*
|--------------------------------------------------------------------------
| Voice Recording State
|--------------------------------------------------------------------------
*/

const isRecording = ref(false);
const recordSeconds = ref(0);
const liveBars = ref(Array(20).fill(30));

let mediaRecorder = null;
let recordingStream = null;
let recordChunks = [];
let recordTimer = null;
let recordStartTime = 0;
let liveBarTimer = null;


/*
|--------------------------------------------------------------------------
| Websocket Channel
|--------------------------------------------------------------------------
*/

const { connected } = useAdminChatChannel({
    onMessage: (payload) => {
        if (
            Number(payload.user_id) ===
            Number(selectedUserId.value)
        ) {
            if (!messages.value.some((m) => m.id === payload.id)) {
                messages.value.push(payload);
                scrollToBottom();
            }
        }

        loadConversations();
    },

    onNewConversation: () => {
        loadConversations();
    },
});


/*
|--------------------------------------------------------------------------
| Formatted Record Time
|--------------------------------------------------------------------------
*/

const formattedRecordTime = computed(() => {
    const m = String(Math.floor(recordSeconds.value / 60)).padStart(2, '0');
    const s = String(recordSeconds.value % 60).padStart(2, '0');
    return `${m}:${s}`;
});


/*
|--------------------------------------------------------------------------
| Load Conversations
|--------------------------------------------------------------------------
*/

const loadConversations = async () => {
    try {
        loadingConversations.value = true;

        const response = await getAdminChats();

        if (response.data?.success) {
            conversations.value = response.data.chats || [];
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
    selectedUserId.value = conversation.user_id;
    selectedUser.value = conversation.user || null;

    await loadConversation(conversation.user_id);
};


/*
|--------------------------------------------------------------------------
| Load Conversation
|--------------------------------------------------------------------------
*/

const loadConversation = async (userId, showLoading = true) => {
    try {
        if (showLoading) {
            loadingMessages.value = true;
        }

        const response = await getAdminConversation(userId);

        if (response.data?.success) {
            messages.value = response.data.messages || [];

            if (!selectedUser.value) {
                const conversation = conversations.value.find(
                    (item) => Number(item.user_id) === Number(userId)
                );

                selectedUser.value = conversation?.user || null;
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
| File Handling
|--------------------------------------------------------------------------
*/

const openFilePicker = () => {
    if (sending.value) return;
    fileInput.value?.click();
};

const onFileSelected = (event) => {
    const file = event.target.files?.[0];

    if (!file) return;

    const maxSize = 20 * 1024 * 1024;

    if (file.size > maxSize) {
        alert('File is too large. Maximum 20 MB.');
        event.target.value = '';
        return;
    }

    selectedFile.value = file;
};

const clearSelectedFile = () => {
    selectedFile.value = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const formatSize = (bytes) => {
    if (!bytes) return '';

    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';

    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};


/*
|--------------------------------------------------------------------------
| Send Message (text + file)
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {
    const messageText = newMessage.value.trim();
    const file = selectedFile.value;

    if (!messageText && !file) return;
    if (!selectedUserId.value || sending.value) return;

    try {
        sending.value = true;

        const response = await sendAdminChatMessage(
            selectedUserId.value,
            messageText,
            file
        );

        if (response.data?.success && response.data?.message) {
            const msg = response.data.message;

            if (!messages.value.some((m) => m.id === msg.id)) {
                messages.value.push(msg);
            }

            newMessage.value = '';
            clearSelectedFile();

            await scrollToBottom();
            await loadConversations();
        }

    } catch (error) {
        console.error(
            'Failed to send admin message:',
            error.response?.data || error.message
        );

        alert(
            error.response?.data?.message ||
            error.response?.data?.errors?.message?.[0] ||
            'Message send nahi ho saka.'
        );

    } finally {
        sending.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| Live Bars (recording waveform)
|--------------------------------------------------------------------------
*/

const startLiveBars = () => {
    stopLiveBars();

    liveBarTimer = setInterval(() => {
        liveBars.value = Array.from({ length: 20 }, () => {
            return Math.floor(Math.random() * 70) + 30;
        });
    }, 120);
};

const stopLiveBars = () => {
    if (liveBarTimer) {
        clearInterval(liveBarTimer);
        liveBarTimer = null;
    }

    liveBars.value = Array(20).fill(30);
};


/*
|--------------------------------------------------------------------------
| Voice Recording — Start
|--------------------------------------------------------------------------
*/

const startRecording = async () => {
    if (isRecording.value || sending.value) return;

    if (!navigator.mediaDevices || !window.MediaRecorder) {
        alert('Voice recording is not supported in this browser.');
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        recordingStream = stream;
        recordChunks = [];
        recordSeconds.value = 0;
        isRecording.value = true;

        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = (event) => {
            if (event.data && event.data.size > 0) {
                recordChunks.push(event.data);
            }
        };

        mediaRecorder.onstop = () => {
            stream.getTracks().forEach((track) => track.stop());
            recordingStream = null;

            if (!recordChunks.length) return;

            const mimeType = mediaRecorder.mimeType || 'audio/webm';
            const blob = new Blob(recordChunks, { type: mimeType });

            sendVoiceMessage(blob, mimeType);
        };

        mediaRecorder.start();

        recordStartTime = Date.now();
        startLiveBars();

        recordTimer = setInterval(() => {
            recordSeconds.value = Math.floor(
                (Date.now() - recordStartTime) / 1000
            );

            if (recordSeconds.value >= 120) {
                stopRecording();
            }
        }, 200);

    } catch (error) {
        console.error('Microphone access error:', error);

        if (error.name === 'NotAllowedError') {
            alert('Microphone permission denied. Please allow it in browser settings.');
        } else if (error.name === 'NotFoundError') {
            alert('No microphone found. Please connect a microphone.');
        } else {
            alert('Unable to start recording.');
        }

        isRecording.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| Stop Recording
|--------------------------------------------------------------------------
*/

const stopRecording = () => {
    if (!isRecording.value) return;

    stopLiveBars();

    if (recordTimer) {
        clearInterval(recordTimer);
        recordTimer = null;
    }

    isRecording.value = false;

    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
};


/*
|--------------------------------------------------------------------------
| Cancel Recording
|--------------------------------------------------------------------------
*/

const cancelRecording = () => {
    if (!isRecording.value) return;

    stopLiveBars();

    recordChunks = [];

    if (recordTimer) {
        clearInterval(recordTimer);
        recordTimer = null;
    }

    isRecording.value = false;

    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
};


/*
|--------------------------------------------------------------------------
| Stop and Send
|--------------------------------------------------------------------------
*/

const stopAndSendRecording = () => {
    if (recordSeconds.value < 1) {
        alert('Recording too short. Please record at least 1 second.');
        return;
    }

    stopRecording();
};


/*
|--------------------------------------------------------------------------
| Send Voice Message
|--------------------------------------------------------------------------
*/

const sendVoiceMessage = async (blob, mimeType) => {
    if (!selectedUserId.value) return;

    let extension = 'webm';
    if (mimeType.includes('mp4')) extension = 'm4a';
    else if (mimeType.includes('ogg')) extension = 'ogg';
    else if (mimeType.includes('wav')) extension = 'wav';
    else if (mimeType.includes('mpeg')) extension = 'mp3';

    const filename = `voice-${Date.now()}.${extension}`;
    const file = new File([blob], filename, { type: mimeType });

    try {
        sending.value = true;

        const response = await sendAdminChatMessage(
            selectedUserId.value,
            '',
            file
        );

        if (response.data?.success && response.data?.message) {
            const msg = response.data.message;

            if (!messages.value.some((m) => m.id === msg.id)) {
                messages.value.push(msg);
            }

            await scrollToBottom();
            await loadConversations();
        }

    } catch (error) {
        console.error('Send voice message error:', error);

        alert(
            error.response?.data?.message ||
            'Voice message send nahi ho saka.'
        );

    } finally {
        sending.value = false;
        recordChunks = [];
    }
};


/*
|--------------------------------------------------------------------------
| Scroll
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
| Open Attachment
|--------------------------------------------------------------------------
*/

const openAttachment = (url) => {
    window.open(url, '_blank', 'noopener,noreferrer');
};


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const getInitials = (user) => {
    if (!user?.name) return '?';

    const parts = user.name.trim().split(/\s+/);

    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }

    return (
        parts[0][0] +
        parts[parts.length - 1][0]
    ).toUpperCase();
};

const formatTime = (date) => {
    if (!date) return '';

    const d = new Date(date);

    if (Number.isNaN(d.getTime())) return '';

    return d.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
    });
};


/*
|--------------------------------------------------------------------------
| Fallback Polling
|--------------------------------------------------------------------------
*/

const startFallbackPolling = () => {
    stopPolling();

    pollingTimer = setInterval(async () => {
        await loadConversations();

        if (selectedUserId.value) {
            await loadConversation(selectedUserId.value, false);
        }
    }, 15000);
};

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
    stopLiveBars();

    if (recordTimer) {
        clearInterval(recordTimer);
        recordTimer = null;
    }

    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    if (recordingStream) {
        recordingStream.getTracks().forEach((t) => t.stop());
        recordingStream = null;
    }
});
</script>


<style scoped>

/* =========================================================
   MAIN CONTAINER
========================================================= */

.admin-chat {
    width: 100%;
    height: 650px;

    display: grid;
    grid-template-columns: 320px 1fr;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;

    overflow: hidden;

    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}


/* =========================================================
   LEFT — USERS PANEL
========================================================= */

.chat-users {
    display: flex;
    flex-direction: column;

    border-right: 1px solid #e5e7eb;

    min-width: 0;
    height: 100%;
    overflow: hidden;
}

.users-header {
    height: 70px;
    padding: 12px 16px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid #e5e7eb;

    flex-shrink: 0;
}

.users-header h2 {
    margin: 0 0 3px;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.users-header p {
    margin: 0;
    font-size: 12px;
    color: #6b7280;
}

.refresh-button {
    width: 34px;
    height: 34px;

    border: 1px solid #d1d5db;
    border-radius: 8px;

    background: #ffffff;

    font-size: 18px;
    line-height: 1;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: background 0.15s ease;
}

.refresh-button:hover {
    background: #f3f4f6;
}


/* =========================================================
   USER LIST
========================================================= */

.user-list {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
}

.user-item {
    width: 100%;

    display: flex;
    align-items: center;
    gap: 12px;

    padding: 12px 16px;

    border: none;
    border-bottom: 1px solid #f1f5f9;

    background: #ffffff;

    text-align: left;
    cursor: pointer;

    transition: background 0.15s ease;
}

.user-item:hover {
    background: #f8fafc;
}

.user-item.active {
    background: #eef2ff;
}

.user-avatar,
.selected-user-avatar {
    flex-shrink: 0;

    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #4f46e5;
    color: #ffffff;

    font-size: 13px;
    font-weight: 700;
}

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
    margin: 4px 0 0;

    color: #6b7280;
    font-size: 12px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-count {
    min-width: 20px;
    height: 20px;

    padding: 0 6px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: #ef4444;
    color: #ffffff;

    font-size: 11px;
    font-weight: 700;
}


/* =========================================================
   RIGHT — CONVERSATION PANEL
========================================================= */

.conversation-panel {
    min-width: 0;

    display: flex;
    flex-direction: column;

    background: #f8fafc;

    height: 100%;
    overflow: hidden;
}

.conversation-header {
    height: 70px;
    padding: 0 18px;

    display: flex;
    align-items: center;
    gap: 12px;

    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;

    flex-shrink: 0;
}

.conversation-header h3 {
    margin: 0 0 3px;

    font-size: 15px;
    font-weight: 700;

    color: #111827;
}

.conversation-header span {
    font-size: 12px;
    color: #6b7280;
}


/* =========================================================
   MESSAGES
========================================================= */

.messages-container {
    flex: 1 1 auto;
    min-height: 0;

    overflow-y: auto;

    padding: 16px;

    background: #f8fafc;

    scroll-behavior: smooth;

    display: flex;
    flex-direction: column;
    gap: 8px;
}

.message-row {
    display: flex;
    margin: 0;
}

.user-row {
    justify-content: flex-start;
}

.admin-row {
    justify-content: flex-end;
}

.message-bubble {
    max-width: 68%;
    padding: 8px 12px;
    border-radius: 12px;
    word-break: break-word;
}

.user-message {
    background: #ffffff;
    color: #1f2937;

    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}

.admin-message {
    background: #4f46e5;
    color: #ffffff;

    border-bottom-right-radius: 4px;
}

.message-sender {
    font-size: 10px;
    font-weight: 700;

    margin-bottom: 3px;

    opacity: 0.7;
}

.message-text {
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-wrap;
}

.message-time {
    margin-top: 4px;

    text-align: right;

    font-size: 10px;
    opacity: 0.65;
}


/* =========================================================
   ATTACHMENTS
========================================================= */

.message-attachment {
    margin-bottom: 6px;
}

.attachment-image {
    max-width: 100%;
    max-height: 220px;

    border-radius: 8px;
    cursor: pointer;

    display: block;
}

.attachment-video {
    max-width: 100%;
    max-height: 220px;

    border-radius: 8px;
    display: block;
}

.attachment-file {
    display: flex;
    align-items: center;
    gap: 8px;

    padding: 8px 10px;

    border-radius: 8px;

    text-decoration: none;
    color: inherit;

    font-size: 12px;
}

.admin-message .attachment-file {
    background: rgba(255, 255, 255, 0.15);
}

.user-message .attachment-file {
    background: #f1f5f9;
}

.file-icon {
    font-size: 16px;
}

.file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-size {
    opacity: 0.7;
    font-size: 10px;
}


/* =========================================================
   FILE PREVIEW
========================================================= */

.file-preview {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 8px 14px;

    background: #eef2ff;
    border-top: 1px solid #e5e7eb;

    font-size: 12px;

    flex-shrink: 0;
}

.file-preview .file-name {
    flex: 1;

    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

    color: #1f2937;
    font-weight: 500;
}

.file-preview .file-size {
    color: #6b7280;
    font-size: 11px;
}

.remove-file {
    width: 24px;
    height: 24px;

    border: none;
    border-radius: 50%;

    background: #dc2626;
    color: #ffffff;

    font-size: 16px;
    line-height: 1;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-file:hover {
    background: #b91c1c;
}

.remove-file:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}


/* =========================================================
   REPLY AREA
========================================================= */

.reply-area {
    display: flex;
    align-items: center;
    gap: 6px;

    padding: 10px 12px;

    background: #ffffff;
    border-top: 1px solid #e5e7eb;

    flex-shrink: 0;
}

.reply-area input[type="text"] {
    flex: 1;

    height: 42px;

    padding: 0 16px;

    border: 1px solid #d1d5db;
    border-radius: 21px;

    outline: none;

    font-size: 14px;
    color: #1f2937;

    min-width: 0;
}

.reply-area input[type="text"]:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.10);
}

.attach-button,
.mic-button {
    width: 42px;
    height: 42px;

    flex-shrink: 0;

    border: none;
    border-radius: 50%;

    background: transparent;
    color: #4f46e5;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: background 0.15s ease, color 0.15s ease;
}

.attach-button:hover:not(:disabled),
.mic-button:hover:not(:disabled) {
    background: #eef2ff;
}

.attach-button:disabled,
.mic-button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.send-button {
    min-width: 76px;
    height: 42px;

    padding: 0 18px;

    flex-shrink: 0;

    border: none;
    border-radius: 21px;

    background: #4f46e5;
    color: #ffffff;

    cursor: pointer;

    font-weight: 600;
    font-size: 14px;

    transition: background 0.15s ease;
}

.send-button:hover:not(:disabled) {
    background: #4338ca;
}

.send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}


/* =========================================================
   RECORDING BAR (WhatsApp-style)
========================================================= */

.recording-bar {
    flex: 1;

    display: flex;
    align-items: center;
    gap: 10px;

    height: 42px;

    padding: 0 8px 0 6px;

    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 21px;

    font-size: 13px;

    min-width: 0;
}

.recording-cancel {
    width: 30px;
    height: 30px;

    flex-shrink: 0;

    border: none;
    border-radius: 50%;

    background: transparent;
    color: #dc2626;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    transition: background 0.15s ease;
}

.recording-cancel:hover {
    background: #fee2e2;
}

.recording-dot {
    width: 10px;
    height: 10px;

    flex-shrink: 0;

    border-radius: 50%;
    background: #dc2626;

    animation: rec-pulse 1s infinite;
}

@keyframes rec-pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.8); }
}

.recording-time {
    font-weight: 700;
    color: #dc2626;

    min-width: 44px;

    font-variant-numeric: tabular-nums;
}

.recording-waveform {
    flex: 1;

    display: flex;
    align-items: center;
    gap: 2px;

    height: 26px;

    overflow: hidden;

    min-width: 0;
}

.rec-bar {
    flex: 1;
    min-width: 2px;
    max-width: 3px;

    border-radius: 2px;

    background: #dc2626;

    transition: height 0.12s ease;

    opacity: 0.75;
}

.recording-stop {
    width: 34px;
    height: 34px;

    flex-shrink: 0;

    border: none;
    border-radius: 50%;

    background: #dc2626;
    color: #ffffff;

    cursor: pointer;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-left: auto;

    transition: background 0.15s ease;
}

.recording-stop:hover:not(:disabled) {
    background: #b91c1c;
}

.recording-stop:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}


/* =========================================================
   EMPTY STATES
========================================================= */

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

.loading-messages,
.empty-messages {
    height: 100%;
    flex: 1;
}


/* =========================================================
   SCROLLBARS
========================================================= */

.user-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar {
    width: 6px;
}

.user-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

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

    .message-bubble {
        max-width: 85%;
    }

    .send-button {
        min-width: 64px;
        padding: 0 12px;
    }
}

</style>