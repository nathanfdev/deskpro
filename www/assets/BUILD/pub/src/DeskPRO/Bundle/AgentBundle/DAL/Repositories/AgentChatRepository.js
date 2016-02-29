import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * AgentChatRepository
 */
export class AgentChatRepository extends ApiRepository {

  /**
   * @param {integer} entityId Entity identity to start the chat
   * @param {string} type type of entity, one of agent, team, department or everyone
   * @returns {Promise} promise
   */
  startChat(entityId, type) {
    return this.api.sendPost(`DP_API/${this.url}/start?follow_redirect`, {type: type, id: entityId});
  }

  /**
   * @param {integer} chatId Chat identity for loading messages
   * @param {string} search String to search through chat messages
   * @param {integer} page Page to load
   * @param {string} order How to order
   * @returns {Promise} promise
   */
  loadMessages(chatId, search = '', page = 1, order = 'date_created') {
    return this.api.sendGet(`DP_API/${this.url}/${chatId}/messages?` + this.compileParams({search, page, order}));
  }

  /**
   * @param {integer} chatId Chat identity where to add message
   * @param {string} message Message content
   * @param {string} uuid unique message identity since we have no ID right now
   * @returns {Promise} promise
   */
  addMessage(chatId, message, uuid) {
    return this.api.sendPost(`DP_API/${this.url}/${chatId}/messages`, {message, uuid});
  }

  /**
   * @returns {Promise} promise
   */
  loadMessagesCount() {
    return this.api.sendGet(`DP_API/${this.url}/messages/count`);
  }

  /**
   * @param {integer[]} ids message ids to mark as status
   * @param {integer} status Message status [0 - new, 1 - sent, 2 - read]
   * @returns {Promise} promise
   */
  markMessages(ids, status) {
    return this.api.sendPut(`DP_API/${this.url}/messages/mark`, {ids, status});
  }
}
