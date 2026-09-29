<template>
    <div>
        <p>Connection: {{ connected ? 'Online' : 'Offline' }}</p>
        <p>Unread: {{ unread }}</p>

        <ul>
            <li v-for="msg in messages" :key="msg.id">
                <strong>{{ msg.user?.name }}:</strong> {{ msg.body }}
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useUserChatChannel } from '@/composables/useChatChannel';

const messages = ref([]);
const unread = ref(0);

const { connected } = useUserChatChannel({
    onMessage: (payload) => {
        messages.value.push(payload.message ?? payload);
    },
    onUnread: (count) => {
        unread.value = count;
    },
});
</script>