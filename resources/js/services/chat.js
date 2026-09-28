import api from './api';

/*
|--------------------------------------------------------------------------
| USER CHAT
|--------------------------------------------------------------------------
*/

export const getChat = () => {
    return api.get('/chat');
};

export const sendChatMessage = (message) => {
    return api.post('/chat/message', { message });
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
    return api.post('/admin/chats/message', {
        user_id: userId,
        message,
    });
};

/*
|--------------------------------------------------------------------------
| DEFAULT EXPORT
|--------------------------------------------------------------------------
*/

export default {
    getChat,
    sendChatMessage,
    getChatUnreadCount,
    getAdminChats,
    getAdminConversation,
    sendAdminChatMessage,
};