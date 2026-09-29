import api from './api';

/*
|--------------------------------------------------------------------------
| USER CHAT
|--------------------------------------------------------------------------
*/

export const getChat = () => {
    return api.get('/chat/messages');
};

export const sendChatMessage = (message = '', attachment = null) => {
    const formData = new FormData();

    if (message && String(message).trim() !== '') {
        formData.append('message', message);
    }

    if (attachment) {
        formData.append('attachment', attachment);
    }

    return api.post('/chat/messages', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
};

export const getChatUnreadCount = () => {
    return api.get('/chat/unread');
};

export const markChatAsRead = () => {
    return api.post('/chat/messages/read');
};

export const sendUserTyping = () => {
    return api.post('/chat/typing');
};

export const deleteChatMessage = (messageId) => {
    return api.delete(`/chat/messages/${messageId}`);
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

export const sendAdminChatMessage = (
    userId,
    message = '',
    attachment = null
) => {
    const formData = new FormData();

    if (message && String(message).trim() !== '') {
        formData.append('message', message);
    }

    if (attachment) {
        formData.append('attachment', attachment);
    }

    return api.post(`/admin/chats/${userId}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
};

export const markAdminChatAsRead = (userId) => {
    return api.post(`/admin/chats/${userId}/read`);
};

export const sendAdminTyping = (userId) => {
    return api.post(`/admin/chats/${userId}/typing`);
};


/*
|--------------------------------------------------------------------------
| CANNED RESPONSES
|--------------------------------------------------------------------------
*/

export const getCannedResponses = () => {
    return api.get('/admin/canned-responses');
};

export const createCannedResponse = (data) => {
    return api.post('/admin/canned-responses', data);
};

export const updateCannedResponse = (id, data) => {
    return api.put(`/admin/canned-responses/${id}`, data);
};

export const deleteCannedResponse = (id) => {
    return api.delete(`/admin/canned-responses/${id}`);
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
    markChatAsRead,
    sendUserTyping,
    deleteChatMessage,

    getAdminChats,
    getAdminConversation,
    sendAdminChatMessage,
    markAdminChatAsRead,
    sendAdminTyping,

    getCannedResponses,
    createCannedResponse,
    updateCannedResponse,
    deleteCannedResponse,
};