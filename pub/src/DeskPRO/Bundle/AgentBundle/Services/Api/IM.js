import DpApi from '../DpApi';

/**
 * Compile parameters into a URL string
 * @param {Object} params - parameters to be compiled
 * @returns {string} - compiled string
 */
function compileParams(params) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}

export function loadRecentChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadChats(missingIds) {
  return DpApi.sendGet('DP_API/agent_chats/?ids=' + missingIds.toArray().join(','));
}

export function loadChat(id) {
  return DpApi.sendGet('DP_API/agent_chats/' + parseInt(id, 10));
}

export function loadMessages(chatId, searchQuery = '', page = 1, order = 'date_created') {
  const params = {
    search: searchQuery,
    page: page,
    order: order
  };
  const compiled = compileParams(params);
  return DpApi.sendGet('DP_API/agent_chats/' + chatId + '/messages?' + compiled);
}

export function startChat(entityId, type) {
  return DpApi.sendPost('DP_API/agent_chats/start?follow_redirect', {type: type, id: entityId});
}

export function addMessage(chatId, message, uuid) {
  return DpApi.sendPost('DP_API/agent_chats/' + chatId + '/messages', {message: message, uuid: uuid});
}

export function loadMessagesCount() {
  return DpApi.sendGet('DP_API/agent_chats/messages/count');
}

export function markMessages(ids, status) {
  return DpApi.sendPut('DP_API/agent_chats/messages/mark', {ids: ids, status: status});
}

