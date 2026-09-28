import { ref, onMounted, onUnmounted } from 'vue';

/*
|--------------------------------------------------------------------------
| USER CHAT CHANNEL
|--------------------------------------------------------------------------
*/

export function useUserChatChannel({ onMessage, onUnread } = {}) {
    const connected = ref(false);

    let channel = null;

    onMounted(() => {
        if (!window.Echo) {
            console.warn('Laravel Echo is not initialized.');
            return;
        }

        let user = {};

        try {
            user = JSON.parse(
                localStorage.getItem('auth_user') || '{}'
            );
        } catch {
            user = {};
        }

        if (!user?.id) {
            return;
        }

        channel = window.Echo
            .private(`chat.user.${user.id}`)
            .listen('.message.sent', (payload) => {
                onMessage?.(payload);
            })
            .listen('.unread.count', (payload) => {
                onUnread?.(payload.count);
            });

        channel.subscribed(() => {
            connected.value = true;
        });
    });

    onUnmounted(() => {
        if (!window.Echo || !channel) {
            return;
        }

        let user = {};

        try {
            user = JSON.parse(
                localStorage.getItem('auth_user') || '{}'
            );
        } catch {
            user = {};
        }

        if (user?.id) {
            window.Echo.leave(`chat.user.${user.id}`);
        }
    });

    return { connected };
}

/*
|--------------------------------------------------------------------------
| ADMIN CHAT CHANNEL
|--------------------------------------------------------------------------
*/

export function useAdminChatChannel({ onMessage, onNewConversation } = {}) {
    const connected = ref(false);

    let channel = null;

    onMounted(() => {
        if (!window.Echo) {
            console.warn('Laravel Echo is not initialized.');
            return;
        }

        channel = window.Echo
            .private('chat.admin')
            .listen('.message.sent', (payload) => {
                onMessage?.(payload);
            })
            .listen('.conversation.created', (payload) => {
                onNewConversation?.(payload);
            });

        channel.subscribed(() => {
            connected.value = true;
        });
    });

    onUnmounted(() => {
        if (window.Echo) {
            window.Echo.leave('chat.admin');
        }
    });

    return { connected };
}