import DpApi from '../DpApi';

/**
 * @return Promise
 */
export function loadRecentChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadMessages(chat_id, searchQuery = '') {
  return DpApi.sendGet('DP_API/agent_chats/' + chat_id + '/messages?search='+searchQuery)
}

export function startChat(entity_id, type) {
  return DpApi.sendPost('DP_API/agent_chats/start', {type: type, id: entity_id})
}

export function addMessage(chat_id, message) {
  return DpApi.sendPost('DP_API/agent_chats/'+chat_id+'/messages', {message: message})
}