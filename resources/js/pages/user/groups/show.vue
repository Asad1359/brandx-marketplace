<template>
    <div class="group-page">

        <div
            v-if="loading"
            class="loading-state"
        >
            Loading group...
        </div>

        <div
            v-else-if="!group"
            class="error-state"
        >
            <p>Group not found or you are not a member.</p>

            <router-link
                to="/dashboard/groups"
                class="back-btn"
            >
                ← Back to Groups
            </router-link>
        </div>

        <div
            v-else
            class="group-chat"
        >

            <!-- =========================================================
                 LEFT SIDEBAR — MEMBERS
            ========================================================== -->

            <aside
                class="group-sidebar"
                :class="{ open: sidebarOpen }"
            >

                <div class="sidebar-header">

                    <div class="group-avatar">
                        {{ group.name?.charAt(0)?.toUpperCase() || 'G' }}
                    </div>

                    <div class="group-info">
                        <h3>{{ group.name }}</h3>
                        <span>{{ members.length }} members</span>
                    </div>

                    <button
                        type="button"
                        class="close-sidebar"
                        @click="sidebarOpen = false"
                    >
                        ×
                    </button>

                </div>

                <div class="members-section">

                    <div class="members-header">
                        <span>Members</span>

                        <button
                            v-if="isGroupAdmin"
                            type="button"
                            class="add-member-btn"
                            title="Add member"
                            @click="openAddMemberModal"
                        >
                            +
                        </button>
                    </div>

                    <div class="members-list">

                        <div
                            v-for="member in members"
                            :key="member.id"
                            class="member-item"
                        >
                            <div class="member-avatar">
                                {{ getInitials(member) }}
                            </div>

                            <div class="member-info">

                                <strong>
                                    {{ member.name }}

                                    <span
                                        v-if="member.pivot?.role === 'admin'"
                                        class="admin-badge"
                                    >
                                        Admin
                                    </span>
                                </strong>

                                <small>{{ member.email }}</small>

                            </div>

                            <button
                                v-if="
                                    isGroupAdmin &&
                                    Number(member.id) !==
                                        Number(currentUserId)
                                "
                                type="button"
                                class="remove-member-btn"
                                title="Remove member"
                                @click="askRemoveMember(member)"
                            >
                                ×
                            </button>
                        </div>

                    </div>

                </div>

                <div class="sidebar-footer">

                    <button
                        type="button"
                        class="leave-btn"
                        @click="askLeaveGroup"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"
                            />
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Leave Group
                    </button>

                </div>

            </aside>


            <!-- Mobile overlay -->
            <div
                v-if="sidebarOpen"
                class="sidebar-overlay"
                @click="sidebarOpen = false"
            ></div>


            <!-- =========================================================
                 RIGHT — CHAT AREA
            ========================================================== -->

            <main class="chat-area">

                <header class="chat-header">

                    <button
                        type="button"
                        class="menu-btn"
                        @click="sidebarOpen = true"
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
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>

                    <div class="header-avatar">
                        {{ group.name?.charAt(0)?.toUpperCase() || 'G' }}
                    </div>

                    <div class="header-info">
                        <h2>{{ group.name }}</h2>
                        <span>{{ members.length }} members</span>
                    </div>

                    <router-link
                        to="/dashboard/groups"
                        class="back-button"
                    >
                        ← Back
                    </router-link>

                </header>


                <!-- MESSAGES -->

                <div
                    ref="messagesContainer"
                    class="messages-container"
                    @click="closeMessageMenu"
                >

                    <div
                        v-if="loadingMessages"
                        class="loading-messages"
                    >
                        Loading messages...
                    </div>

                    <div
                        v-else-if="messages.length === 0"
                        class="empty-messages"
                    >
                        <div class="empty-icon">💬</div>
                        <p>No messages yet</p>
                        <span>Start the conversation!</span>
                    </div>

                    <template v-else>

                        <div
                            v-for="message in messages"
                            :key="message.id"
                            class="message-row"
                            :class="{
                                'own-message':
                                    isOwnMessage(message),
                                'other-message':
                                    !isOwnMessage(message)
                            }"
                        >
                            <div class="message-wrapper">

                                <div
                                    v-if="!isOwnMessage(message)"
                                    class="message-sender"
                                >
                                    {{
                                        message.sender?.name ||
                                        'Unknown'
                                    }}
                                </div>

                                <div
                                    class="message-bubble"
                                    :class="{
                                        own:
                                            isOwnMessage(message),
                                        other:
                                            !isOwnMessage(message),
                                        deleted:
                                            message.deleted_at
                                    }"
                                    @contextmenu.prevent="
                                        openMenu($event, message)
                                    "
                                >

                                    <!-- Deleted -->

                                    <div
                                        v-if="message.deleted_at"
                                        class="deleted-message"
                                    >
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
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="10"
                                            />
                                            <line
                                                x1="4.93"
                                                y1="4.93"
                                                x2="19.07"
                                                y2="19.07"
                                            />
                                        </svg>

                                        <span>
                                            This message was deleted
                                        </span>
                                    </div>

                                    <template v-else>

                                        <!-- Reply quote -->

                                        <div
                                            v-if="message.parent"
                                            class="message-reply-quote"
                                        >
                                            <span class="quote-sender">
                                                {{
                                                    message.parent
                                                        .sender_type ===
                                                    'user'
                                                        ? (
                                                            message
                                                                .parent
                                                                .sender
                                                                ?.name ||
                                                            'User'
                                                        )
                                                        : 'Admin'
                                                }}
                                            </span>

                                            <span class="quote-text">
                                                {{
                                                    message
                                                        .parent
                                                        .message ||
                                                    message
                                                        .parent
                                                        .attachment_name ||
                                                    'Attachment'
                                                }}
                                            </span>
                                        </div>

                                        <!-- Attachment -->

                                        <div
                                            v-if="
                                                message.attachment_url
                                            "
                                            class="message-attachment"
                                        >
                                            <img
                                                v-if="
                                                    message.is_image
                                                "
                                                :src="
                                                    message.attachment_url
                                                "
                                                :alt="
                                                    message.attachment_name
                                                "
                                                class="attachment-image"
                                                @click="
                                                    openLightbox(
                                                        message.attachment_url
                                                    )
                                                "
                                            />

                                            <video
                                                v-else-if="
                                                    message.is_video
                                                "
                                                :src="
                                                    message.attachment_url
                                                "
                                                controls
                                                class="attachment-video"
                                            ></video>

                                            <VoicePlayer
                                                v-else-if="
                                                    message.is_audio
                                                "
                                                :src="
                                                    message.attachment_url
                                                "
                                                :own="
                                                    isOwnMessage(
                                                        message
                                                    )
                                                "
                                            />

                                            <a
                                                v-else
                                                :href="
                                                    message.attachment_url
                                                "
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="attachment-file"
                                            >
                                                <span class="file-icon">
                                                    📎
                                                </span>

                                                <span class="file-name">
                                                    {{
                                                        message
                                                            .attachment_name
                                                    }}
                                                </span>

                                                <span class="file-size">
                                                    {{
                                                        formatSize(
                                                            message
                                                                .attachment_size
                                                        )
                                                    }}
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

                                    </template>

                                    <!-- Meta -->

                                    <div class="message-meta">

                                        <span
                                            v-if="
                                                message.is_starred
                                            "
                                            class="star-indicator"
                                        >
                                            ★
                                        </span>

                                        <span class="message-time">
                                            {{
                                                formatTime(
                                                    message.created_at
                                                )
                                            }}
                                        </span>

                                        <span
                                            v-if="
                                                isOwnMessage(
                                                    message
                                                ) &&
                                                !message.deleted_at
                                            "
                                            class="read-tick"
                                            :class="{
                                                read:
                                                    !!message.read_at
                                            }"
                                        >

                                            <svg
                                                v-if="
                                                    message.read_at
                                                "
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
                                                <polyline
                                                    points="1 12 5 16 11 10"
                                                />
                                                <polyline
                                                    points="9 12 13 16 22 6"
                                                />
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
                                                <polyline
                                                    points="20 6 9 17 4 12"
                                                />
                                            </svg>

                                        </span>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </template>

                </div>


                <!-- File preview -->

                <div
                    v-if="selectedFile"
                    class="file-preview"
                >
                    <span class="file-icon">📎</span>

                    <span class="file-name">
                        {{ selectedFile.name }}
                    </span>

                    <span class="file-size">
                        {{ formatSize(selectedFile.size) }}
                    </span>

                    <button
                        type="button"
                        class="remove-file"
                        :disabled="sending"
                        @click="clearSelectedFile"
                    >
                        ×
                    </button>
                </div>


                <!-- Reply preview -->

                <div
                    v-if="replyTo"
                    class="reply-preview"
                >
                    <div class="reply-preview-content">

                        <strong>
                            Replying to
                            {{
                                isOwnMessage(replyTo)
                                    ? 'yourself'
                                    : (
                                        replyTo.sender
                                            ?.name ||
                                        'User'
                                    )
                            }}
                        </strong>

                        <span>
                            {{
                                replyTo.message ||
                                replyTo.attachment_name ||
                                'Attachment'
                            }}
                        </span>

                    </div>

                    <button
                        type="button"
                        class="reply-cancel"
                        @click="cancelReply"
                    >
                        ×
                    </button>
                </div>


                <!-- Emoji picker -->

                <div
                    v-if="showEmojiPicker"
                    class="emoji-picker-wrapper"
                >
                    <EmojiPicker
                        :native="true"
                        :disable-skin-tones="true"
                        theme="light"
                        @select="onEmojiSelect"
                    />
                </div>


                <!-- Input -->

                <form
                    class="input-area"
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
                        class="icon-btn"
                        title="Emoji"
                        :disabled="sending"
                        @click="
                            showEmojiPicker = !showEmojiPicker
                        "
                    >
                        😀
                    </button>

                    <button
                        v-if="!isRecording"
                        type="button"
                        class="icon-btn"
                        title="Attach file"
                        :disabled="sending"
                        @click="openFilePicker"
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
                            <line x1="12" y1="5" x2="12" y2="19"/>
                            <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                    </button>

                    <button
                        v-if="!isRecording && !selectedFile"
                        type="button"
                        class="icon-btn"
                        title="Record voice"
                        :disabled="
                            sending || !!newMessage.trim()
                        "
                        @click="startRecording"
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
                            <path
                                d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"
                            />
                            <path
                                d="M19 10v2a7 7 0 0 1-14 0v-2"
                            />
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
                    />

                    <button
                        v-if="!isRecording"
                        type="submit"
                        class="send-btn"
                        :disabled="
                            sending ||
                            (
                                !newMessage.trim() &&
                                !selectedFile
                            )
                        "
                    >
                        <span v-if="sending">...</span>
                        <span v-else>Send</span>
                    </button>

                    <!-- Recording bar -->

                    <div
                        v-if="isRecording"
                        class="recording-bar"
                    >
                        <button
                            type="button"
                            class="recording-cancel"
                            @click="cancelRecording"
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
                                <line
                                    x1="18"
                                    y1="6"
                                    x2="6"
                                    y2="18"
                                />
                                <line
                                    x1="6"
                                    y1="6"
                                    x2="18"
                                    y2="18"
                                />
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
                                :style="{
                                    height: height + '%'
                                }"
                            ></span>
                        </div>

                        <button
                            type="button"
                            class="recording-stop"
                            :disabled="recordSeconds < 1"
                            @click="stopAndSendRecording"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="18"
                                height="18"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M2 21l21-9L2 3v7l15 2-15 2z"
                                />
                            </svg>
                        </button>
                    </div>

                </form>

            </main>

        </div>


        <!-- Message context menu -->

        <Teleport to="body">
            <div
                v-if="activeMenu && activeMenuMessage"
                class="message-menu"
                :style="menuStyle"
                @click.stop
            >
                <button
                    type="button"
                    class="menu-item"
                    @click="startReply"
                >
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
                        <polyline
                            points="9 17 4 12 9 7"
                        />
                        <path
                            d="M20 18v-2a4 4 0 0 0-4-4H4"
                        />
                    </svg>
                    Reply
                </button>

                <button
                    v-if="isOwnMessage(activeMenuMessage)"
                    type="button"
                    class="menu-item danger"
                    @click="deleteMessage"
                >
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
                        <polyline
                            points="3 6 5 6 21 6"
                        />
                        <path
                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                        />
                    </svg>
                    Delete Message
                </button>
            </div>
        </Teleport>


        <!-- Add member modal -->

        <div
            v-if="showAddMemberModal"
            class="modal-overlay"
            @click.self="closeAddMemberModal"
        >
            <div class="modal">

                <h3>Add Member</h3>

                <input
                    v-model="memberSearch"
                    type="text"
                    placeholder="Search by name or email..."
                />

                <div class="user-list">

                    <div
                        v-for="user in filteredAvailableUsers"
                        :key="user.id"
                        class="user-option"
                        @click="addMember(user)"
                    >
                        <div class="user-avatar">
                            {{ getInitials(user) }}
                        </div>

                        <div>
                            <strong>{{ user.name }}</strong>
                            <small>{{ user.email }}</small>
                        </div>
                    </div>

                    <div
                        v-if="
                            filteredAvailableUsers.length === 0
                        "
                        class="no-users"
                    >
                        No users available to add.
                    </div>

                </div>

                <div class="modal-actions">
                    <button
                        type="button"
                        @click="closeAddMemberModal"
                    >
                        Close
                    </button>
                </div>

            </div>
        </div>


        <!-- Image lightbox -->

        <ImageLightbox
            v-model="lightboxOpen"
            :src="lightboxSrc"
        />

    </div>
</template>


<script setup>
import {
    ref,
    computed,
    onMounted,
    onUnmounted,
    nextTick,
} from 'vue';

import { useRoute, useRouter } from 'vue-router';

import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css';

import {
    getGroupDetails,
    getGroupMessages,
    sendGroupMessage,
    addGroupMember,
    removeGroupMember,
    leaveGroup,
    deleteChatMessage,
} from '../../../services/chat';

import { useUserChatChannel } from '../../../composables/useChatChannel';
import { authState } from '../../../stores/auth';

import VoicePlayer from '../../../components/VoicePlayer.vue';
import ImageLightbox from '../../../components/ImageLightbox.vue';


const route = useRoute();
const router = useRouter();

const conversationId = route.params.id;


/*
|--------------------------------------------------------------------------
| STATE
|--------------------------------------------------------------------------
*/

const group = ref(null);
const members = ref([]);
const messages = ref([]);
const newMessage = ref('');
const selectedFile = ref(null);

const loading = ref(true);
const loadingMessages = ref(false);
const sending = ref(false);

const sidebarOpen = ref(false);
const showEmojiPicker = ref(false);

const messagesContainer = ref(null);
const fileInput = ref(null);

const replyTo = ref(null);

const activeMenu = ref(null);
const activeMenuMessage = ref(null);
const menuStyle = ref({ top: '0px', left: '0px' });

const lightboxOpen = ref(false);
const lightboxSrc = ref('');

const showAddMemberModal = ref(false);
const memberSearch = ref('');

let pollingTimer = null;


/*
|--------------------------------------------------------------------------
| VOICE
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
| COMPUTED
|--------------------------------------------------------------------------
*/

const currentUserId = computed(() => authState.user?.id);

const isGroupAdmin = computed(() => {
    const me = members.value.find(
        (m) => Number(m.id) === Number(currentUserId.value)
    );

    return me?.pivot?.role === 'admin';
});

const formattedRecordTime = computed(() => {
    const m = String(
        Math.floor(recordSeconds.value / 60)
    ).padStart(2, '0');

    const s = String(
        recordSeconds.value % 60
    ).padStart(2, '0');

    return `${m}:${s}`;
});

const availableUsers = ref([]);

const filteredAvailableUsers = computed(() => {
    const keyword = memberSearch.value
        .trim()
        .toLowerCase();

    const memberIds = members.value.map((m) =>
        Number(m.id)
    );

    return availableUsers.value
        .filter(
            (u) => !memberIds.includes(Number(u.id))
        )
        .filter((u) => {
            if (!keyword) return true;

            return (
                String(u.name || '')
                    .toLowerCase()
                    .includes(keyword) ||
                String(u.email || '')
                    .toLowerCase()
                    .includes(keyword)
            );
        })
        .slice(0, 50);
});


/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

const isOwnMessage = (message) => {
    return (
        Number(message.sender_id) ===
        Number(currentUserId.value)
    );
};

const getInitials = (user) => {
    if (!user?.name) return '?';

    const parts = user.name.trim().split(/\s+/);

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

const formatTime = (date) => {
    if (!date) return '';

    const d = new Date(date);

    if (Number.isNaN(d.getTime())) return '';

    return d.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatSize = (bytes) => {
    if (!bytes) return '';

    if (bytes < 1024) return bytes + ' B';

    if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
};


/*
|--------------------------------------------------------------------------
| LOAD GROUP
|--------------------------------------------------------------------------
*/

const loadGroup = async () => {
    try {
        loading.value = true;

        const response = await getGroupDetails(
            conversationId
        );

        if (response.data?.success) {
            group.value = response.data.group;

            members.value =
                response.data.group.members || [];
        }
    } catch (error) {
        console.error('Load group error:', error);
        group.value = null;
    } finally {
        loading.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| LOAD MESSAGES
|--------------------------------------------------------------------------
*/

const loadMessages = async (showLoading = true) => {
    try {
        if (showLoading) {
            loadingMessages.value = true;
        }

        const response = await getGroupMessages(
            conversationId
        );

        if (response.data?.success) {
            messages.value =
                response.data.messages || [];

            if (response.data.group?.members) {
                members.value =
                    response.data.group.members;
            }

            await scrollToBottom();
        }
    } catch (error) {
        console.error('Load messages error:', error);
    } finally {
        if (showLoading) {
            loadingMessages.value = false;
        }
    }
};


/*
|--------------------------------------------------------------------------
| LOAD AVAILABLE USERS
|--------------------------------------------------------------------------
*/

const loadAvailableUsers = async () => {
    try {
        const response = await fetch('/api/user/all', {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });

        const data = await response.json();

        if (data.success) {
            availableUsers.value = data.users || [];
        }
    } catch (error) {
        console.error('Load users error:', error);
    }
};


/*
|--------------------------------------------------------------------------
| WEBSOCKET
|--------------------------------------------------------------------------
*/

const { connected } = useUserChatChannel({
    onMessage: (payload) => {
        if (
            Number(payload.conversation_id) !==
            Number(conversationId)
        ) {
            return;
        }

        if (
            !messages.value.some(
                (m) => m.id === payload.id
            )
        ) {
            messages.value.push(payload);
            scrollToBottom();
        }
    },

    onDeleted: (payload) => {
        const msg = messages.value.find(
            (m) => m.id === payload.id
        );

        if (msg) {
            msg.deleted_at =
                new Date().toISOString();
            msg.message = null;
            msg.attachment_url = null;
        }
    },
});


/*
|--------------------------------------------------------------------------
| SEND MESSAGE
|--------------------------------------------------------------------------
*/

const sendMessage = async () => {
    const text = newMessage.value.trim();
    const file = selectedFile.value;

    if (!text && !file) return;
    if (sending.value) return;

    try {
        sending.value = true;

        const response = await sendGroupMessage(
            conversationId,
            text,
            file,
            replyTo.value?.id || null
        );

        if (
            response.data?.success &&
            response.data?.message
        ) {
            const msg = response.data.message;

            if (
                !messages.value.some(
                    (m) => m.id === msg.id
                )
            ) {
                messages.value.push(msg);
            }

            newMessage.value = '';
            clearSelectedFile();
            showEmojiPicker.value = false;
            replyTo.value = null;

            await scrollToBottom();
        }
    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to send message.'
        );
    } finally {
        sending.value = false;
    }
};


/*
|--------------------------------------------------------------------------
| FILE
|--------------------------------------------------------------------------
*/

const openFilePicker = () => {
    if (sending.value) return;
    fileInput.value?.click();
};

const onFileSelected = (event) => {
    const file = event.target.files?.[0];

    if (!file) return;

    if (file.size > 20 * 1024 * 1024) {
        alert('File too large. Max 20 MB.');
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


/*
|--------------------------------------------------------------------------
| REPLY / DELETE
|--------------------------------------------------------------------------
*/

const startReply = () => {
    if (!activeMenuMessage.value) return;

    replyTo.value = activeMenuMessage.value;
    closeMessageMenu();
};

const cancelReply = () => {
    replyTo.value = null;
};

const openMenu = (event, message) => {
    if (message.deleted_at) return;

    activeMenu.value = message.id;
    activeMenuMessage.value = message;

    const menuWidth = 220;
    const menuHeight = 120;
    const padding = 10;

    let x = event.clientX;
    let y = event.clientY;

    if (x + menuWidth > window.innerWidth - padding) {
        x = window.innerWidth - menuWidth - padding;
    }

    if (y + menuHeight > window.innerHeight - padding) {
        y = y - menuHeight;
    }

    if (x < padding) x = padding;
    if (y < padding) y = padding;

    menuStyle.value = {
        top: y + 'px',
        left: x + 'px',
    };
};

const closeMessageMenu = () => {
    activeMenu.value = null;
    activeMenuMessage.value = null;
};

const deleteMessage = async () => {
    const message = activeMenuMessage.value;

    if (!message) return;

    closeMessageMenu();

    if (!confirm('Delete this message?')) return;

    try {
        await deleteChatMessage(message.id, 'all');

        message.deleted_at =
            new Date().toISOString();
        message.message = null;
        message.attachment_url = null;
    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to delete.'
        );
    }
};


/*
|--------------------------------------------------------------------------
| MEMBER MANAGEMENT
|--------------------------------------------------------------------------
*/

const openAddMemberModal = async () => {
    showAddMemberModal.value = true;
    memberSearch.value = '';

    if (availableUsers.value.length === 0) {
        await loadAvailableUsers();
    }
};

const closeAddMemberModal = () => {
    showAddMemberModal.value = false;
};

const addMember = async (user) => {
    try {
        await addGroupMember(
            conversationId,
            user.id
        );

        await loadGroup();

        showAddMemberModal.value = false;
    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to add member.'
        );
    }
};

const askRemoveMember = async (member) => {
    if (
        !confirm(
            `Remove ${member.name} from the group?`
        )
    ) {
        return;
    }

    try {
        await removeGroupMember(
            conversationId,
            member.id
        );

        await loadGroup();
    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to remove member.'
        );
    }
};

const askLeaveGroup = async () => {
    if (
        !confirm(
            'Leave this group? You will need to be re-added to come back.'
        )
    ) {
        return;
    }

    try {
        await leaveGroup(conversationId);

        router.push({
            name: 'user.groups',
        });
    } catch (error) {
        alert(
            error.response?.data?.message ||
            'Unable to leave group.'
        );
    }
};


/*
|--------------------------------------------------------------------------
| EMOJI
|--------------------------------------------------------------------------
*/

const onEmojiSelect = (emoji) => {
    newMessage.value +=
        emoji.i || emoji.emoji || '';
};


/*
|--------------------------------------------------------------------------
| LIGHTBOX
|--------------------------------------------------------------------------
*/

const openLightbox = (url) => {
    lightboxSrc.value = url;
    lightboxOpen.value = true;
};


/*
|--------------------------------------------------------------------------
| VOICE
|--------------------------------------------------------------------------
*/

const startLiveBars = () => {
    stopLiveBars();

    liveBarTimer = setInterval(() => {
        liveBars.value = Array.from(
            { length: 20 },
            () => Math.floor(Math.random() * 70) + 30
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

    if (
        !navigator.mediaDevices ||
        !window.MediaRecorder
    ) {
        alert('Voice not supported.');
        return;
    }

    try {
        const stream =
            await navigator.mediaDevices.getUserMedia(
                { audio: true }
            );

        recordingStream = stream;
        recordChunks = [];
        recordSeconds.value = 0;
        isRecording.value = true;

        mediaRecorder = new MediaRecorder(stream);

        mediaRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) {
                recordChunks.push(e.data);
            }
        };

        mediaRecorder.onstop = () => {
            stream
                .getTracks()
                .forEach((t) => t.stop());

            recordingStream = null;

            if (!recordChunks.length) return;

            const mimeType =
                mediaRecorder.mimeType ||
                'audio/webm';

            const blob = new Blob(recordChunks, {
                type: mimeType,
            });

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
        alert('Mic error.');
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

    if (
        mediaRecorder &&
        mediaRecorder.state !== 'inactive'
    ) {
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

    if (
        mediaRecorder &&
        mediaRecorder.state !== 'inactive'
    ) {
        mediaRecorder.stop();
    }
};

const stopAndSendRecording = () => {
    if (recordSeconds.value < 1) return;

    stopRecording();
};

const sendVoiceMessage = async (blob, mimeType) => {
    let ext = 'webm';

    if (mimeType.includes('mp4')) ext = 'm4a';
    else if (mimeType.includes('ogg')) ext = 'ogg';
    else if (mimeType.includes('wav')) ext = 'wav';
    else if (mimeType.includes('mpeg')) ext = 'mp3';

    const filename = `voice-${Date.now()}.${ext}`;

    const file = new File([blob], filename, {
        type: mimeType,
    });

    try {
        sending.value = true;

        const response = await sendGroupMessage(
            conversationId,
            '',
            file,
            replyTo.value?.id || null
        );

        if (
            response.data?.success &&
            response.data?.message
        ) {
            const msg = response.data.message;

            if (
                !messages.value.some(
                    (m) => m.id === msg.id
                )
            ) {
                messages.value.push(msg);
            }

            replyTo.value = null;

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
| SCROLL
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
| POLLING FALLBACK
|--------------------------------------------------------------------------
*/

const startPolling = () => {
    stopPolling();

    pollingTimer = setInterval(() => {
        loadMessages(false);
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
| LIFECYCLE
|--------------------------------------------------------------------------
*/

onMounted(async () => {
    await loadGroup();
    await loadMessages();

    setTimeout(() => {
        if (!connected.value) startPolling();
    }, 1500);
});

onUnmounted(() => {
    stopPolling();
    stopLiveBars();

    if (recordTimer) clearInterval(recordTimer);

    if (
        mediaRecorder &&
        mediaRecorder.state !== 'inactive'
    ) {
        mediaRecorder.stop();
    }

    if (recordingStream) {
        recordingStream
            .getTracks()
            .forEach((t) => t.stop());
    }
});
</script>


<style scoped>

/* =========================================================
   MAIN
========================================================= */

.group-page {
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.loading-state,
.error-state {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    height: 100%;
    color: #6b7280;
}

.back-btn {
    margin-top: 15px;
    padding: 10px 18px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}

.group-chat {
    display: grid;
    grid-template-columns: 300px 1fr;
    height: 100%;
    background: #ffffff;
    overflow: hidden;
}


/* =========================================================
   SIDEBAR
========================================================= */

.group-sidebar {
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e5e7eb;
    background: #ffffff;
    height: 100%;
    overflow: hidden;
}

.sidebar-header {
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.group-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background:
        linear-gradient(135deg, #6366f1, #06b6d4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    flex-shrink: 0;
}

.group-info {
    flex: 1;
    min-width: 0;
}

.group-info h3 {
    margin: 0;
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.group-info span {
    font-size: 12px;
    color: #6b7280;
}

.close-sidebar {
    display: none;
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 50%;
    background: #f3f4f6;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
}

.members-section {
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.members-header {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #f1f5f9;
}

.add-member-btn {
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 50%;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.add-member-btn:hover {
    background: #dbeafe;
}

.members-list {
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0;
}

.member-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    transition: background 0.15s ease;
}

.member-item:hover {
    background: #f8fafc;
}

.member-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #4f46e5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.member-info {
    flex: 1;
    min-width: 0;
    font-size: 13px;
}

.member-info strong {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #111827;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.member-info small {
    color: #6b7280;
    font-size: 11px;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-badge {
    padding: 1px 6px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 10px;
    font-weight: 700;
    border-radius: 4px;
}

.remove-member-btn {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 50%;
    background: #f3f4f6;
    color: #6b7280;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
    flex-shrink: 0;
}

.remove-member-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}

.sidebar-footer {
    padding: 12px;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.leave-btn {
    width: 100%;
    padding: 10px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #dc2626;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.leave-btn:hover {
    background: #fee2e2;
}


/* =========================================================
   CHAT AREA
========================================================= */

.chat-area {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-width: 0;
    overflow: hidden;
}

.chat-header {
    height: 68px;
    padding: 0 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    flex-shrink: 0;
}

.menu-btn {
    display: none;
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 8px;
    background: #f3f4f6;
    cursor: pointer;
    align-items: center;
    justify-content: center;
}

.header-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background:
        linear-gradient(135deg, #6366f1, #06b6d4);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    flex-shrink: 0;
}

.header-info {
    flex: 1;
    min-width: 0;
}

.header-info h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-info span {
    font-size: 12px;
    color: #6b7280;
}

.back-button {
    padding: 8px 14px;
    border-radius: 8px;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.back-button:hover {
    background: #e5e7eb;
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

.own-message {
    justify-content: flex-end;
}

.other-message {
    justify-content: flex-start;
}

.message-wrapper {
    max-width: 68%;
    display: flex;
    flex-direction: column;
}

.own-message .message-wrapper {
    align-items: flex-end;
}

.message-sender {
    font-size: 11px;
    font-weight: 700;
    color: #4f46e5;
    margin-bottom: 3px;
    padding-left: 4px;
}

.message-bubble {
    padding: 8px 12px;
    border-radius: 12px;
    word-break: break-word;
    position: relative;
}

.message-bubble.own {
    background: #4f46e5;
    color: #ffffff;
    border-bottom-right-radius: 4px;
}

.message-bubble.other {
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

.message-reply-quote {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 6px 10px;
    margin-bottom: 6px;
    border-left: 3px solid currentColor;
    border-radius: 6px;
    background: rgba(0, 0, 0, 0.05);
    font-size: 11px;
    opacity: 0.85;
}

.message-bubble.own .message-reply-quote {
    background: rgba(255, 255, 255, 0.15);
}

.quote-sender {
    font-weight: 700;
}

.quote-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.message-text {
    font-size: 14px;
    line-height: 1.4;
    white-space: pre-wrap;
}

.message-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 4px;
    font-size: 10px;
    opacity: 0.65;
}

.star-indicator {
    color: #f59e0b;
}

.read-tick {
    display: inline-flex;
    align-items: center;
}

.read-tick.read {
    color: #ffffff;
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

.message-bubble.own .attachment-file {
    background: rgba(255, 255, 255, 0.15);
}

.message-bubble.other .attachment-file {
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
   EMPTY / LOADING
========================================================= */

.loading-messages,
.empty-messages {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    height: 100%;
    color: #6b7280;
    text-align: center;
}

.empty-icon {
    font-size: 50px;
    margin-bottom: 12px;
}

.empty-messages p {
    margin: 0 0 5px;
    font-weight: 600;
    color: #374151;
}


/* =========================================================
   PREVIEWS
========================================================= */

.file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
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
    color: white;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.reply-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #eef2ff;
    border-top: 1px solid #e5e7eb;
    border-left: 3px solid #4f46e5;
    flex-shrink: 0;
}

.reply-preview-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 12px;
}

.reply-preview-content strong {
    color: #4f46e5;
    font-weight: 700;
    font-size: 11px;
}

.reply-preview-content span {
    color: #6b7280;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.reply-cancel {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 50%;
    background: #dc2626;
    color: white;
    font-size: 14px;
    cursor: pointer;
    line-height: 1;
    flex-shrink: 0;
}


/* =========================================================
   EMOJI PICKER
========================================================= */

.emoji-picker-wrapper {
    position: absolute;
    bottom: 70px;
    right: 12px;
    z-index: 100;
    box-shadow:
        0 12px 40px rgba(0, 0, 0, 0.2);
    border-radius: 10px;
    overflow: hidden;
    background: white;
}


/* =========================================================
   INPUT
========================================================= */

.input-area {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 12px;
    background: #ffffff;
    border-top: 1px solid #e5e7eb;
    flex-shrink: 0;
    position: relative;
}

.input-area input[type="text"] {
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

.input-area input[type="text"]:focus {
    border-color: #4f46e5;
    box-shadow:
        0 0 0 2px rgba(79, 70, 229, 0.10);
}

.icon-btn {
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
    transition: background 0.15s ease;
    font-size: 20px;
}

.icon-btn:hover:not(:disabled) {
    background: #eef2ff;
}

.icon-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.send-btn {
    min-width: 76px;
    height: 42px;
    padding: 0 18px;
    flex-shrink: 0;
    border: none;
    border-radius: 21px;
    background: #4f46e5;
    color: white;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
}

.send-btn:hover:not(:disabled) {
    background: #4338ca;
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}


/* =========================================================
   RECORDING
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
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.4;
        transform: scale(0.8);
    }
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
    opacity: 0.75;
}

.recording-stop {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: #dc2626;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
}


/* =========================================================
   CONTEXT MENU
========================================================= */

.message-menu {
    position: fixed;
    z-index: 2147483647;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow:
        0 8px 24px rgba(0, 0, 0, 0.18);
    overflow: hidden;
    min-width: 200px;
    padding: 4px 0;
}

.message-menu .menu-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 16px;
    border: none;
    background: transparent;
    font-size: 13px;
    color: #374151;
    cursor: pointer;
    text-align: left;
    font-weight: 500;
}

.message-menu .menu-item:hover {
    background: #f3f4f6;
}

.message-menu .menu-item.danger {
    color: #dc2626;
}

.message-menu .menu-item.danger:hover {
    background: #fee2e2;
}


/* =========================================================
   MODAL
========================================================= */

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    z-index: 9999;
}

.modal {
    background: white;
    padding: 24px;
    border-radius: 14px;
    width: 100%;
    max-width: 480px;
}

.modal h3 {
    margin: 0 0 16px;
}

.modal input[type="text"] {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    outline: none;
    font-size: 14px;
    margin-bottom: 12px;
    box-sizing: border-box;
}

.user-list {
    max-height: 320px;
    overflow-y: auto;
    margin-bottom: 16px;
}

.user-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s ease;
}

.user-option:hover {
    background: #f3f4f6;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #4f46e5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
}

.user-option strong {
    display: block;
    font-size: 14px;
}

.user-option small {
    color: #6b7280;
    font-size: 12px;
}

.no-users {
    text-align: center;
    padding: 20px;
    color: #6b7280;
    font-size: 13px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid #e5e7eb;
}

.modal-actions button {
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    background: #f3f4f6;
    color: #374151;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
}

.modal-actions button:hover {
    background: #e5e7eb;
}


/* =========================================================
   SCROLLBAR
========================================================= */

.members-list::-webkit-scrollbar,
.messages-container::-webkit-scrollbar,
.user-list::-webkit-scrollbar {
    width: 6px;
}

.members-list::-webkit-scrollbar-thumb,
.messages-container::-webkit-scrollbar-thumb,
.user-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .group-chat {
        grid-template-columns: 1fr;
    }

    .group-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 280px;
        height: 100vh;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        box-shadow:
            4px 0 20px rgba(0, 0, 0, 0.15);
    }

    .group-sidebar.open {
        transform: translateX(0);
    }

    .close-sidebar {
        display: flex;
    }

    .menu-btn {
        display: flex;
    }

    .sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    .message-wrapper {
        max-width: 85%;
    }

}

</style>