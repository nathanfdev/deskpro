import DpApi from '../DpApi';

export function loadRecentChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadChats(missingIds) {
  return DpApi.sendGet('DP_API/agent_chats/?ids=' + missingIds.toArray().join(','));
}

export function loadChat(id) {
  return DpApi.sendGet('DP_API/agent_chats/' + parseInt(id, 10));
}

export function loadMessages(chatId, searchQuery = '', page = null) {
  const params = {
    search: searchQuery
  };
  if(page) {
    params.page = page;
  }
  const compiled = compileParams(params);
  return DpApi.sendGet('DP_API/agent_chats/' + chatId + '/messages?' + compiled);
}

export function startChat(entityId, type) {
  return DpApi.sendPost('DP_API/agent_chats/start?follow_redirect', {type: type, id: entityId});
}

export function addMessage(chatId, message) {
  return DpApi.sendPost('DP_API/agent_chats/' + chatId + '/messages', {message: message});
}

export function loadMessagesCount() {
  return DpApi.sendGet('DP_API/agent_chats/messages/count');
}

export function markMessages(ids) {
  return DpApi.sendPatch('DP_API/agent_chats/messages/mark', {ids: ids});
}

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