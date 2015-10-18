import DpApi from '../DpApi';

export function loadRecentChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadMessages(chatId, searchQuery = '') {
  return DpApi.sendGet('DP_API/agent_chats/' + chatId + '/messages?search=' + searchQuery);
}

export function startChat(entityId, type) {
  return DpApi.sendPost('DP_API/agent_chats/start', {type: type, id: entityId});
}

export function addMessage(chatId, message) {
  return DpApi.sendPost('DP_API/agent_chats/' + chatId + '/messages', {message: message});
}