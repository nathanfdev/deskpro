import DpApi from '../DpApi';

export function loadRecentChats() {
  return DpApi.sendGet('DP_API/agent_chats/recent');
}

export function loadChats(missingIds) {
  return DpApi.sendGet('DP_API/agent_chats/?ids=' + missingIds.toArray().join(','));
}

export function loadMessages(chatId, searchQuery = '') {
  return DpApi.sendGet('DP_API/agent_chats/' + chatId + '/messages?search=' + searchQuery);
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