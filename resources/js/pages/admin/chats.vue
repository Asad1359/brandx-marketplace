<template>
    <div class="admin-chats-page">
        <AdminChat />
    </div>
</template>

<script setup>
import AdminChat from '../../components/AdminChat.vue';
</script>

<style scoped>
.admin-chats-page {
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
    padding: 12px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    width: 100%;
}
</style>