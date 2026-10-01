import { ref, onMounted, onUnmounted } from 'vue';

function waitForEcho(timeout = 5000) {
    return new Promise((resolve, reject) => {
        if (window.Echo) return resolve(window.Echo);

        const start = Date.now();
        const timer = setInterval(() => {
            if (window.Echo) {
                clearInterval(timer);
                resolve(window.Echo);
            } else if (Date.now() - start > timeout) {
                clearInterval(timer);
                reject(new Error('Laravel Echo is not initialized.'));
            }
        }, 50);
    });
}

function getAuthUser() {
    try {
        return JSON.parse(localStorage.getItem('auth_user') || '{}');
    } catch {
        return {};
    }
}

/*
|--------------------------------------------------------------------------
| USER CHAT CHANNEL
|--------------------------------------------------------------------------
*/

export function useUserChatChannel({
    onMessage,
    onUnread,
    onRead,
    onDeleted,
    onDeletedForMe,
    onStarred,
    onBlocked,
    onTyping,
} = {}) {
    const connected = ref(false);
    let channel = null;
    let userId = null;

    onMounted(async () => {
        const user = getAuthUser();

        if (!user?.id) {
            console.warn('useUserChatChannel: no auth_user.');
            return;
        }

        userId = user.id;

        try {
            const echo = await waitForEcho();

            channel = echo
                .private(`chat.user.${userId}`)
                .listen('.message.sent', (payload) => onMessage?.(payload))
                .listen('.unread.count', (payload) => onUnread?.(payload.count))
                .listen('.message.read', (payload) => onRead?.(payload))
                .listen('.message.deleted', (payload) => onDeleted?.(payload))
                .listen('.message.deletedForMe', (payload) => onDeletedForMe?.(payload))
                .listen('.message.starred', (payload) => onStarred?.(payload))
                .listen('.conversation.blocked', (payload) => onBlocked?.(payload))
                .listen('.user.typing', (payload) => {
                    if (payload.sender_type !== 'user') {
                        onTyping?.(payload);
                    }
                });

            channel.subscribed(() => {
                connected.value = true;
            });
        } catch (e) {
            console.error('useUserChatChannel:', e.message);
        }
    });

    onUnmounted(() => {
        if (window.Echo && userId) {
            window.Echo.leave(`chat.user.${userId}`);
        }
        channel = null;
        connected.value = false;
    });

    return { connected };
}

/*
|--------------------------------------------------------------------------
| ADMIN CHAT CHANNEL
|--------------------------------------------------------------------------
*/

export function useAdminChatChannel({
    onMessage,
    onNewConversation,
    onRead,
    onDeleted,
    onDeletedForMe,
    onStarred,
    onBlocked,
    onTyping,
} = {}) {
    const connected = ref(false);
    let channel = null;

    onMounted(async () => {
        try {
            const echo = await waitForEcho();

            channel = echo
                .private('chat.admin')
                .listen('.message.sent', (payload) => onMessage?.(payload))
                .listen('.conversation.created', (payload) => onNewConversation?.(payload))
                .listen('.message.read', (payload) => onRead?.(payload))
                .listen('.message.deleted', (payload) => onDeleted?.(payload))
                .listen('.message.deletedForMe', (payload) => onDeletedForMe?.(payload))
                .listen('.message.starred', (payload) => onStarred?.(payload))
                .listen('.conversation.blocked', (payload) => onBlocked?.(payload))
                .listen('.user.typing', (payload) => {
                    if (payload.sender_type !== 'admin') {
                        onTyping?.(payload);
                    }
                });

            channel.subscribed(() => {
                connected.value = true;
            });
        } catch (e) {
            console.error('useAdminChatChannel:', e.message);
        }
    });

    onUnmounted(() => {
        if (window.Echo) {
            window.Echo.leave('chat.admin');
        }
        channel = null;
        connected.value = false;
    });

    return { connected };
}