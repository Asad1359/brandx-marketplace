<template>
    <Teleport to="body">
        <div class="chat-widget">

            <!-- Floating Button -->
            <button
                class="chat-button"
                :class="{ hidden: isOpen }"
                @click="toggleChat"
                aria-label="Open chat"
            >
                <span class="chat-icon">💬</span>

                <span
                    v-if="unreadCount > 0 && !isOpen"
                    class="unread-badge"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </button>


            <!-- Chat Window -->
            <div v-if="isOpen" class="chat-window">

                <div class="chat-header">
                    <div>
                        <h3>Support Chat</h3>
                        <span class="online-status">
                            ● {{ connected ? 'Live' : 'Online' }}
                        </span>
                    </div>

                    <button
                        class="header-close"
                        @click="closeChat"
                        type="button"
                        aria-label="Close"
                    >
                        ×
                    </button>
                </div>


                <div
                    ref="messagesContainer"
                    class="messages-container"
                    @click="closeMessageMenu"
                >
                    <div v-if="loading" class="loading-message">
                        Loading chat...
                    </div>

                    <div
                        v-else-if="messages.length === 0"
                        class="empty-message"
                    >
                        <div class="empty-icon">💬</div>
                        <p>No messages yet.</p>
                        <span>Send a message to contact admin.</span>
                    </div>

                    <template v-else>
                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="message-row"
                            :class="{
                                'user-message-row': message.sender_type === 'user',
                                'admin-message-row': message.sender_type === 'admin'
                            }"
                        >
                            <div
                                class="message-bubble-wrapper"
                                @contextmenu.prevent="openMenu($event, message)"
                            >
                                <div
                                    class="message-bubble"
                                    :class="{
                                        'user-message': message.sender_type === 'user',
                                        'admin-message': message.sender_type === 'admin',
                                        'deleted': message.deleted_at
                                    }"
                                >
                                    <div v-if="message.deleted_at" class="deleted-message">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                        </svg>
                                        <span>This message was deleted</span>
                                    </div>

                                    <template v-else>
                                        <div
                                            v-if="message.attachment_url"
                                            class="message-attachment"
                                        >
                                            <img
                                                v-if="message.is_image"
                                                :src="message.attachment_url"
                                                :alt="message.attachment_name"
                                                class="attachment-image"
                                                @click="openAttachment(message.attachment_url)"
                                            />

                                            <video
                                                v-else-if="message.is_video"
                                                :src="message.attachment_url"
                                                controls
                                                class="attachment-video"
                                            ></video>

                                            <VoicePlayer
                                                v-else-if="message.is_audio"
                                                :src="message.attachment_url"
                                                :own="message.sender_type === 'user'"
                                            />

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

                                        <div
                                            v-if="message.message"
                                            class="message-text"
                                        >
                                            {{ message.message }}
                                        </div>
                                    </template>

                                    <div class="message-meta">
                                        <span class="message-time">
                                            {{ formatTime(message.created_at) }}
                                        </span>

                                        <span
                                            v-if="message.sender_type === 'user' && !message.deleted_at"
                                            class="read-tick"
                                            :class="{ read: !!message.read_at }"
                                        >
                                            <svg
                                                v-if="message.read_at"
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="14"
                                                height="14"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <polyline points="1 12 5 16 11 10"/>
                                                <polyline points="9 12 13 16 22 6"/>
                                            </svg>

                                            <svg
                                                v-else
                                                xmlns="http://www.w3.org/2000/svg"
                                                width="14"
                                                height="14"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2.5"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            >
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </span>
                                    </div>
                                </div>

                                <div
                                    v-if="activeMenu === message.id && !message.deleted_at && message.sender_type === 'user'"
                                    class="message-menu"
                                    @click.stop
                                >
                                    <button type="button" @click="deleteMessage(message)">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="14"
                                            height="14"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="adminIsTyping" class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </template>
                </div>


                <div v-if="selectedFile" class="file-preview">
                    <span class="file-icon">📎</span>
                    <span class="file-name">{{ selectedFile.name }}</span>
                    <span class="file-size">{{ formatSize(selectedFile.size) }}</span>

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


                <div v-if="showEmojiPicker" class="emoji-picker-wrapper">
                    <EmojiPicker
                        :native="true"
                        :disable-skin-tones="true"
                        theme="light"
                        @select="onEmojiSelect"
                    />
                </div>


                <form
                    class="chat-input-area"
                    @submit.prevent="sendMessage"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*,video/*,audio/*,application/pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
                        style="display: none;"
                        @change="onFileSelected"
                    />

                    <button
                        v-if="!isRecording"
                        type="button"
                        class="emoji-button"
                        @click="toggleEmojiPicker"
                        :disabled="sending"
                        title="Emoji"
                        aria-label="Emoji"
                    >
                        😀
                    </button>

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

                    <button
                        v-if="!isRecording && !selectedFile"
                        type="button"
                        class="mic-button"
                        @click="startRecording"
                        :disabled="sending || !!newMessage.trim()"
                        title="Record voice"
                        aria-label="Record voice"
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

                    <input
                        v-if="!isRecording"
                        v-model="newMessage"
                        type="text"
                        placeholder="Type a message..."
                        maxlength="5000"
                        :disabled="sending"
                        autocomplete="off"
                        @input="onInputTyping"
                    />

                    <button
                        v-if="!isRecording"
                        type="submit"
                        class="send-button"
                        :disabled="sending || (!newMessage.trim() && !selectedFile)"
                        aria-label="Send"
                    >
                        <span v-if="sending">...</span>
                        <span v-else>➤</span>
                    </button>


                    <div v-if="isRecording" class="recording-bar">
                        <button
                            type="button"
                            class="recording-cancel"
                            @click="cancelRecording"
                            aria-label="Cancel"
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

                        <span class="recording-dot"></span>

                        <span class="recording-time">
                            {{ formattedRecordTime }}
                        </span>

                        <div class="recording-waveform">
                            <span
                                v-for="(height, i) in liveBars"
                                :key="i"
                                class="rec-bar"
                                :style="{ height: height + '%' }"
                            ></span>
                        </div>

                        <button
                            type="button"
                            class="recording-stop"
                            @click="stopAndSendRecording"
                            :disabled="recordSeconds < 1"
                            aria-label="Send"
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

            </div>

        </div>
    </Teleport>
</template>


<script setup>
import {
    ref,
    computed,
    onMounted,
    onUnmounted,
    nextTick,
} from 'vue';

import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css';

import {
    getChat,
    getChatUnreadCount,
    sendChatMessage,
    markChatAsRead,
    sendUserTyping,
    deleteChatMessage,
} from '../services/chat';

import { useUserChatChannel } from '../composables/useChatChannel';
import VoicePlayer from './VoicePlayer.vue';


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
const fileInput = ref(null);
const selectedFile = ref(null);

const showEmojiPicker = ref(false);
const adminIsTyping = ref(false);
const activeMenu = ref(null);

let unreadPollTimer = null;
let messagePollTimer = null;
let typingTimer = null;
let typingDebounce = null;


/*
|--------------------------------------------------------------------------
| Voice state
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
| Websocket
|--------------------------------------------------------------------------
*/

const { connected } = useUserChatChannel({
    onMessage: (payload) => {
        const id = payload.id ?? payload.message?.id;
        if (!id) return;

        if (!messages.value.some((m) => m.id === id)) {
            messages.value.push(payload);
            scrollToBottom();
        }

        if (isOpen.value) {
            unreadCount.value = 0;
            markChatAsRead().catch(() => {});
        } else {
            unreadCount.value++;
        }
    },

    onUnread: (count) => {
        unreadCount.value = Number(count) || 0;
    },

    onRead: (payload) => {
        const msg = messages.value.find((m) => m.id === payload.id);
        if (msg) {
            msg.read_at = payload.read_at;
        }
    },

    onDeleted: (payload) => {
        const msg = messages.value.find((m) => m.id === payload.id);
        if (msg) {
            msg.deleted_at = new Date().toISOString();
            msg.message = null;
            msg.attachment_url = null;
        }
    },

    onTyping: (payload) => {
        if (payload.sender_type === 'admin') {
            adminIsTyping.value = true;

            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                adminIsTyping.value = false;
            }, 3000);
        }
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
            messages.value = response.data.messages || [];
            await scrollToBottom();

            if (isOpen.value) {
                await markChatAsRead().catch(() => {});
            }
        }
    } catch (error) {
        console.error('Failed to load chat:', error);
    } finally {
        loading.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| Load Unread Count
|--------------------------------------------------------------------------
*/

const loadUnreadCount = async () => {
    try {
        const response = await getChatUnreadCount();

        if (response.data?.success) {
            const newCount = Number(response.data.count) || 0;

            if (newCount > unreadCount.value && isOpen.value) {
                await loadChat();
                unreadCount.value = 0;
                return;
            }

            unreadCount.value = newCount;
        }
    } catch (error) {
        console.error('Unread count error:', error);
    }
};


/*
|--------------------------------------------------------------------------
| Toggle / Close
|--------------------------------------------------------------------------
*/

const toggleChat = async () => {
    if (isOpen.value) {
        closeChat();
        return;
    }

    isOpen.value = true;
    await loadChat();
    unreadCount.value = 0;
};

const closeChat = () => {
    isOpen.value = false;
    showEmojiPicker.value = false;
    activeMenu.value = null;
};


/*
|--------------------------------------------------------------------------
| Typing indicator
|--------------------------------------------------------------------------
*/

const onInputTyping = () => {
    if (typingDebounce) return;

    typingDebounce = setTimeout(() => {
        typingDebounce = null;
    }, 2000);

    sendUserTyping().catch(() => {});
};


/*
|--------------------------------------------------------------------------
| Emoji
|--------------------------------------------------------------------------
*/

const toggleEmojiPicker = () => {
    showEmojiPicker.value = !showEmojiPicker.value;
};

const onEmojiSelect = (emoji) => {
    newMessage.value += emoji.i || emoji.emoji || '';
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
        alert('File too large. Max 20 MB.');
        event.target.value = '';
        return;
    }

    selectedFile.value = file;
};

const clearSelectedFile = () => {
    selectedFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const formatSize = (bytes) => {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};


/*
|--------------------------------------------------------------------------
| Send Message
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {
    const messageText = newMessage.value.trim();
    const file = selectedFile.value;

    if (!messageText && !file) return;
    if (sending.value) return;

    try {
        sending.value = true;

        const response = await sendChatMessage(messageText, file);

        if (response.data?.success && response.data?.message) {
            const msg = response.data.message;

            if (!messages.value.some((m) => m.id === msg.id)) {
                messages.value.push(msg);
            }

            newMessage.value = '';
            clearSelectedFile();
            showEmojiPicker.value = false;

            await scrollToBottom();
        }
    } catch (error) {
        console.error('Send error:', error);
        alert(error.response?.data?.message || 'Message send nahi ho saka.');
    } finally {
        sending.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| Delete Message
|--------------------------------------------------------------------------
*/

const openMenu = (event, message) => {
    if (message.deleted_at) return;
    if (message.sender_type !== 'user') return;

    activeMenu.value = message.id;
};

const closeMessageMenu = () => {
    activeMenu.value = null;
};

const deleteMessage = async (message) => {
    activeMenu.value = null;

    if (!confirm('Delete this message?')) return;

    try {
        await deleteChatMessage(message.id);

        message.deleted_at = new Date().toISOString();
        message.message = null;
        message.attachment_url = null;
    } catch (error) {
        alert(error.response?.data?.message || 'Unable to delete.');
    }
};


/*
|--------------------------------------------------------------------------
| Attachment open
|--------------------------------------------------------------------------
*/

const openAttachment = (url) => {
    window.open(url, '_blank', 'noopener,noreferrer');
};


/*
|--------------------------------------------------------------------------
| Voice recording
|--------------------------------------------------------------------------
*/

const startLiveBars = () => {
    stopLiveBars();

    liveBarTimer = setInterval(() => {
        liveBars.value = Array.from({ length: 20 }, () =>
            Math.floor(Math.random() * 70) + 30
        );
    }, 120);
};

const stopLiveBars = () => {
    if (liveBarTimer) {
        clearInterval(liveBarTimer);
        liveBarTimer = null;
    }
    liveBars.value = Array(20).fill(30);
};

const startRecording = async () => {
    if (isRecording.value || sending.value) return;

    if (!navigator.mediaDevices || !window.MediaRecorder) {
        alert('Voice recording not supported.');
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

        recordingStream = stream;
        recordChunks = [];
        recordSeconds.value = 0;
        isRecording.value = true;

        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) recordChunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            stream.getTracks().forEach((t) => t.stop());
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
            recordSeconds.value = Math.floor((Date.now() - recordStartTime) / 1000);
            if (recordSeconds.value >= 120) stopRecording();
        }, 200);
    } catch (error) {
        console.error('Mic error:', error);
        alert('Unable to start recording.');
        isRecording.value = false;
    }
};

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

const stopAndSendRecording = () => {
    if (recordSeconds.value < 1) {
        alert('Record at least 1 second.');
        return;
    }
    stopRecording();
};

const sendVoiceMessage = async (blob, mimeType) => {
    let ext = 'webm';
    if (mimeType.includes('mp4')) ext = 'm4a';
    else if (mimeType.includes('ogg')) ext = 'ogg';
    else if (mimeType.includes('wav')) ext = 'wav';
    else if (mimeType.includes('mpeg')) ext = 'mp3';

    const filename = `voice-${Date.now()}.${ext}`;
    const file = new File([blob], filename, { type: mimeType });

    try {
        sending.value = true;
        const response = await sendChatMessage('', file);

        if (response.data?.success && response.data?.message) {
            const msg = response.data.message;
            if (!messages.value.some((m) => m.id === msg.id)) {
                messages.value.push(msg);
            }
            await scrollToBottom();
        }
    } catch (error) {
        alert('Voice send failed.');
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
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};


/*
|--------------------------------------------------------------------------
| Format Time
|--------------------------------------------------------------------------
*/

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};


/*
|--------------------------------------------------------------------------
| Fallback Polling
|--------------------------------------------------------------------------
*/

const startPolling = () => {
    stopPolling();
    unreadPollTimer = setInterval(loadUnreadCount, 15000);
    messagePollTimer = setInterval(() => {
        if (isOpen.value) loadChat();
    }, 15000);
};

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
    await loadUnreadCount();

    setTimeout(() => {
        if (!connected.value) startPolling();
    }, 1500);
});

onUnmounted(() => {
    stopPolling();
    stopLiveBars();

    if (recordTimer) clearInterval(recordTimer);
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }
    if (recordingStream) {
        recordingStream.getTracks().forEach((t) => t.stop());
    }

    if (typingTimer) clearTimeout(typingTimer);
    if (typingDebounce) clearTimeout(typingDebounce);
});
</script>


<style scoped>

/* =========================================================
   WIDGET CONTAINER
========================================================= */

.chat-widget {
    position: fixed;
    right: 24px;
    bottom: 24px;
    z-index: 99999;
    font-family: Arial, Helvetica, sans-serif;
    pointer-events: none;
}

.chat-widget > * {
    pointer-events: auto;
}


/* =========================================================
   FLOATING BUTTON
========================================================= */

.chat-button {
    position: relative;
    width: 58px;
    height: 58px;
    border: none;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    font-size: 25px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.chat-button:hover {
    transform: scale(1.06);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.30);
}

.chat-button.hidden {
    opacity: 0;
    pointer-events: none;
    transform: scale(0.5);
}

.chat-icon {
    font-size: 25px;
    line-height: 1;
}


/* =========================================================
   UNREAD BADGE
========================================================= */

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
    color: #ffffff;
    border-radius: 50%;
    font-size: 11px;
    font-weight: bold;
    border: 2px solid #ffffff;
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}


/* =========================================================
   CHAT WINDOW
========================================================= */

.chat-window {
    position: fixed;
    right: 24px;
    bottom: 96px;
    width: 380px;
    height: 540px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
    border: 1px solid #e5e7eb;
    z-index: 100000;
}


/* =========================================================
   HEADER
========================================================= */

.chat-header {
    height: 68px;
    padding: 0 16px;
    background: #4f46e5;
    color: #ffffff;
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
    color: #ffffff;
    font-size: 28px;
    cursor: pointer;
    padding: 5px 8px;
    line-height: 1;
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

.user-message-row {
    justify-content: flex-end;
}

.admin-message-row {
    justify-content: flex-start;
}

.message-bubble-wrapper {
    position: relative;
    max-width: 78%;
}

.message-bubble {
    padding: 8px 12px;
    border-radius: 12px;
    word-break: break-word;
}

.user-message {
    background: #4f46e5;
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

.admin-message {
    background: #ffffff;
    color: #1f2937;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}

.message-bubble.deleted {
    opacity: 0.6;
    font-style: italic;
    background: #f1f5f9 !important;
    color: #64748b !important;
}

.deleted-message {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.message-text {
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-wrap;
}

.message-time {
    font-size: 10px;
    opacity: 0.65;
}

.message-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
}

.read-tick {
    display: inline-flex;
    align-items: center;
    color: #94a3b8;
}

.read-tick.read {
    color: #4f46e5;
}

.user-message .read-tick {
    color: rgba(255, 255, 255, 0.6);
}

.user-message .read-tick.read {
    color: #ffffff;
}

.message-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    z-index: 20;
    overflow: hidden;
}

.message-menu button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 14px;
    border: none;
    background: transparent;
    font-size: 13px;
    color: #dc2626;
    cursor: pointer;
    transition: background 0.15s ease;
}

.message-menu button:hover {
    background: #fee2e2;
}

.typing-indicator {
    display: flex;
    gap: 4px;
    align-self: flex-start;
    padding: 8px 14px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    border-bottom-left-radius: 4px;
    width: fit-content;
}

.typing-indicator span {
    width: 7px;
    height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typing-bounce 1.4s infinite;
}

.typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
.typing-indicator span:nth-child(3) { animation-delay: 0.4s; }

@keyframes typing-bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-5px); }
}

.message-attachment { margin-bottom: 6px; }

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

.user-message .attachment-file { background: rgba(255, 255, 255, 0.15); }
.admin-message .attachment-file { background: #f1f5f9; }

.file-icon { font-size: 16px; }
.file-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-size { opacity: 0.7; font-size: 10px; }

.loading-message {
    text-align: center;
    color: #6b7280;
    padding: 30px 10px;
    font-size: 14px;
}

.empty-message {
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #6b7280;
}

.empty-icon { font-size: 40px; margin-bottom: 10px; }
.empty-message p { margin: 0 0 5px; font-weight: 600; color: #374151; }
.empty-message span { font-size: 13px; }

.file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
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
}

.file-preview .file-size { color: #6b7280; font-size: 11px; }

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

.remove-file:hover { background: #b91c1c; }
.remove-file:disabled { opacity: 0.5; cursor: not-allowed; }

.emoji-picker-wrapper {
    position: absolute;
    bottom: 70px;
    right: 12px;
    z-index: 100;
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
    border-radius: 10px;
    overflow: hidden;
    background: white;
}

.chat-input-area {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
    position: relative;
}

.chat-input-area input[type="text"] {
    flex: 1;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    padding: 0 14px;
    outline: none;
    font-size: 14px;
    min-width: 0;
}

.chat-input-area input[type="text"]:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.10);
}

.emoji-button,
.attach-button,
.mic-button {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: transparent;
    color: #4f46e5;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
    font-size: 20px;
}

.emoji-button:hover:not(:disabled),
.attach-button:hover:not(:disabled),
.mic-button:hover:not(:disabled) {
    background: #eef2ff;
}

.emoji-button:disabled,
.attach-button:disabled,
.mic-button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.send-button {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
}

.send-button:hover:not(:disabled) { background: #4338ca; }
.send-button:disabled { opacity: 0.5; cursor: not-allowed; }

.recording-bar {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    height: 40px;
    padding: 0 8px 0 6px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 20px;
    font-size: 13px;
    min-width: 0;
}

.recording-cancel {
    width: 28px;
    height: 28px;
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

.recording-cancel:hover { background: #fee2e2; }

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
    width: 32px;
    height: 32px;
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

.recording-stop:hover:not(:disabled) { background: #b91c1c; }
.recording-stop:disabled { opacity: 0.5; cursor: not-allowed; }

.messages-container::-webkit-scrollbar { width: 6px; }
.messages-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

@media (max-width: 480px) {
    .chat-widget { right: 15px; bottom: 15px; }

    .chat-window {
        position: fixed;
        left: 10px;
        right: 10px;
        bottom: 84px;
        width: auto;
        height: auto;
        max-height: calc(100vh - 110px);
    }

    .chat-button {
        width: 54px;
        height: 54px;
        font-size: 22px;
    }

    .chat-icon { font-size: 22px; }
}

</style>