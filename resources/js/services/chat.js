// resources/js/services/chat.js
import axios from 'axios';

/*
|--------------------------------------------------------------------------
| Axios instance
|--------------------------------------------------------------------------
*/
const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// CSRF token attach karein
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    api.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

/*
|==========================================================================
| USER CHAT (Support Chat) — ChatWidget.vue
|==========================================================================
*/

export function getChat() {
    return api.get('/chat/support');
}

export function getChatUnreadCount() {
    return api.get('/chat/unread-count');
}

export function sendChatMessage(message, file = null, parentId = null) {
    const formData = new FormData();
    if (message) formData.append('message', message);
    if (file) formData.append('attachment', file);
    if (parentId) formData.append('parent_id', parentId);

    return api.post('/chat/support/messages', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}

export function markChatAsRead() {
    return api.post('/chat/support/read');
}

export function sendUserTyping() {
    return api.post('/chat/support/typing');
}

// ⭐ User apna message delete kare (sirf 1 baar)
export function deleteChatMessage(id, scope = 'all') {
    return api.delete(`/chat/messages/${id}?scope=${scope}`);
}

export function searchUserChat(query) {
    return api.get(`/chat/search?q=${encodeURIComponent(query)}`);
}

export function rateChat(rating, feedback = '') {
    return api.post('/chat/support/rate', { rating, feedback });
}

export function toggleStarMessage(id) {
    return api.post(`/chat/messages/${id}/star`);
}

/*
|==========================================================================
| 1-TO-1 CONVERSATIONS
|==========================================================================
*/

export function getConversations() {
    return api.get('/chat/conversations');
}

export function getConversation(id) {
    return api.get(`/chat/conversations/${id}`);
}

export function startConversation(userId) {
    return api.post('/chat/conversations', { user_id: userId });
}

export function getConversationMessages(id) {
    return api.get(`/chat/conversations/${id}/messages`);
}

export function sendConversationMessage(id, message, file = null, parentId = null) {
    const formData = new FormData();
    if (message) formData.append('message', message);
    if (file) formData.append('attachment', file);
    if (parentId) formData.append('parent_id', parentId);

    return api.post(`/chat/conversations/${id}/messages`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}

export function markConversationAsRead(id) {
    return api.post(`/chat/conversations/${id}/read`);
}

export function sendConversationTyping(id) {
    return api.post(`/chat/conversations/${id}/typing`);
}

export function rateConversation(id, rating, feedback = '') {
    return api.post(`/chat/conversations/${id}/rate`, { rating, feedback });
}

/*
|==========================================================================
| GROUPS
|==========================================================================
*/

export function getUserGroups() {
    return api.get('/chat/groups');
}

export function getGroups() {
    return api.get('/chat/groups');
}

export function createGroup(payload) {
    return api.post('/chat/groups', payload);
}

export function getGroup(id) {
    return api.get(`/chat/groups/${id}`);
}

export function getGroupDetails(id) {
    return api.get(`/chat/groups/${id}`);
}

export function getGroupMessages(id) {
    return api.get(`/chat/groups/${id}/messages`);
}

export function sendGroupMessage(id, message, file = null, parentId = null) {
    const formData = new FormData();
    if (message) formData.append('message', message);
    if (file) formData.append('attachment', file);
    if (parentId) formData.append('parent_id', parentId);

    return api.post(`/chat/groups/${id}/messages`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}

export function addGroupMember(groupId, userId) {
    return api.post(`/chat/groups/${groupId}/members`, { user_id: userId });
}

export function removeGroupMember(groupId, userId) {
    return api.delete(`/chat/groups/${groupId}/members/${userId}`);
}

export function leaveGroup(groupId) {
    return api.post(`/chat/groups/${groupId}/leave`);
}

export function updateGroup(groupId, payload) {
    return api.put(`/chat/groups/${groupId}`, payload);
}

export function deleteGroup(groupId) {
    return api.delete(`/chat/groups/${groupId}`);
}

export function markGroupAsRead(groupId) {
    return api.post(`/chat/groups/${groupId}/read`);
}

export function sendGroupTyping(groupId) {
    return api.post(`/chat/groups/${groupId}/typing`);
}

/*
|==========================================================================
| ADMIN CHAT — AdminChat.vue
|==========================================================================
*/

export function getAdminChats() {
    return api.get('/admin/chat/conversations');
}

export function getAdminConversation(id) {
    return api.get(`/admin/chat/conversations/${id}`);
}

export function sendAdminChatMessage(id, message, file = null, parentId = null) {
    const formData = new FormData();
    if (message) formData.append('message', message);
    if (file) formData.append('attachment', file);
    if (parentId) formData.append('parent_id', parentId);

    return api.post(`/admin/chat/conversations/${id}/messages`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}

export function markAdminChatAsRead(id) {
    return api.post(`/admin/chat/conversations/${id}/read`);
}

export function sendAdminTyping(id) {
    return api.post(`/admin/chat/conversations/${id}/typing`);
}

// ⭐ Admin kisi bhi message ko delete kare (sirf 1 baar)
export function deleteAdminChatMessage(id, scope = 'all') {
    return api.delete(`/admin/chat/messages/${id}?scope=${scope}`);
}

export function getCannedResponses() {
    return api.get('/admin/chat/canned-responses');
}

export function searchAdminChat(query) {
    return api.get(`/admin/chat/search?q=${encodeURIComponent(query)}`);
}

export function assignConversation(id) {
    return api.post(`/admin/chat/conversations/${id}/assign`);
}

export function blockConversation(id, reason = '') {
    return api.post(`/admin/chat/conversations/${id}/block`, { reason });
}

export function unblockConversation(id) {
    return api.post(`/admin/chat/conversations/${id}/unblock`);
}

export function clearConversation(id) {
    return api.post(`/admin/chat/conversations/${id}/clear`);
}

export function archiveConversation(id) {
    return api.post(`/admin/chat/conversations/${id}/archive`);
}

export function unarchiveConversation(id) {
    return api.post(`/admin/chat/conversations/${id}/unarchive`);
}

export function deleteConversation(id) {
    return api.delete(`/admin/chat/conversations/${id}`);
}

export function getAdminRatings() {
    return api.get('/admin/chat/ratings');
}

/*
|==========================================================================
| EXPORT DEFAULT
|==========================================================================
*/
export default api;