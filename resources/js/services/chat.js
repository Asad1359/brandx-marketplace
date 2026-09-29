import api from './api';

/*
|--------------------------------------------------------------------------
| USER CHAT
|--------------------------------------------------------------------------
*/

export const getChat = () => {
    return api.get('/chat/messages');
};

export const sendChatMessage = (message) => {
    return api.post('/chat/messages', { message });
};

export const getChatUnreadCount = () => {
    return api.get('/chat/unread');
};

/*
|--------------------------------------------------------------------------
| ADMIN CHAT
|--------------------------------------------------------------------------
*/

export const getAdminChats = () => {
    return api.get('/admin/chats');
};

export const getAdminConversation = (userId) => {
    return api.get(`/admin/chats/${userId}`);
};

export const sendAdminChatMessage = (userId, message) => {
    return api.post(`/admin/chats/${userId}`, { message });
};

export default {
    getChat,
    sendChatMessage,
    getChatUnreadCount,
    getAdminChats,
    getAdminConversation,
    sendAdminChatMessage,
};