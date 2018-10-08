import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/**
 * AgentChatRepository
 */
export class AgentChatRepository extends ApiRepository {

  /**
   * @param {integer} entityId Entity identity to start the chat
   * @param {string} type type of entity, one of agent, team, department or everyone
   * @param {string} groupName used only for group type.
   * @returns {Promise} promise
   */
  startChat(entityId, type, groupName = '') {
    const params = { type };
    if ((typeof entityId === 'number' && parseInt(entityId, 10) > 0) && type !== 'group') {
      params.participant = entityId;
    } else if (Array.isArray(entityId) && type === 'group') {
      if (groupName) {
        params.name = groupName;
      }
      params.participant = entityId;
    }

    return this.api.sendPost(`DP_API/${this.url}?follow_location=1`, params);
  }

  /**
   * @param {integer} chatId Chat identity for loading messages
   * @param {string} search String to search through chat messages
   * @param {integer} page Page to load
   * @param {string} order How to order
   * @returns {Promise} promise
   */
  loadMessages(chatId, search = '', page = 1, order = 'date_created') {
    return this.api.sendGet(`DP_API/${this.url}/${chatId}/messages?${compileParams({ search, page, order })}`);
  }

  /**
   * @param {integer} chatId Chat identity where to add message
   * @param {string} message Message content
   * @param {string} uuid unique message identity since we have no ID right now
   * @returns {Promise} promise
   */
  addMessage(chatId, message, uuid, blobs) {
    return this.api.sendPost(`DP_API/${this.url}/${chatId}/messages`, { message, uuid, blobs });
  }

  /**
   * @returns {Promise} promise
   */
  loadMessagesCount() {
    return this.api.sendGet(
      `DP_API/${this.url}/messages/counts?group_by=chat&status[]=0&status[]=1&index_group_by=1&not_my=1`
    );
  }

  /**
   * @param {integer} chatId Chat identity
   * @param {integer[]} ids message ids to mark as status
   * @param {integer} status Message status [0 - new, 1 - sent, 2 - read]
   * @returns {Promise} promise
   */
  markMessages(chatId, ids, status) {
    return this.api.sendPut(`DP_API/${this.url}/${chatId}/messages/mark`, { ids, status });
  }

  /**
   * @param {integer} chatId Chat identity
   * @returns {Promise} promise
   */
  markAllMessagesAsRead(chatId) {
    return this.api.sendPut(`DP_API/${this.url}/${chatId}/messages/mark_all`);
  }

  updateChat(chatId, ids, name) {
    return this.api.sendPut(`DP_API/${this.url}/${chatId}`, { type: 'group', participant: ids, name });
  }

  hideChat(chatId) {
    return this.api.sendPut(`DP_API/${this.url}/${chatId}/hide`);
  }

  revealChat(chatId) {
    return this.api.sendPut(`DP_API/${this.url}/${chatId}/reveal`);
  }

  deleteGroup(chatId) {
    return this.api.sendDelete(`DP_API/${this.url}/${chatId}/delete`);
  }

  leaveGroup(chatId) {
    return this.api.sendDelete(`DP_API/${this.url}/${chatId}/leave`);
  }

  updateChatsOrder(order) {
    return this.api.sendPost(`DP_API/${this.url}/chats_order`, { chats_order: order });
  }
}

export default AgentChatRepository;
