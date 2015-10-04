import DpApi from '../DpApi';

/**
 * @return Promise
 */
export function loadLatest() {
  return DpApi.sendGet('DP_API/agent_chats');
}

export function loadMessages(chat_id, searchQuery = '') {
  "use strict";
  return DpApi.sendGet('DP_API/agent_chats/' + chat_id + '/messages?search='+searchQuery)
}

export function startChat(entity_id, type) {
  "use strict";
  return DpApi.sendPost('DP_API/agent_chats/start', {agent: entity_id})
}