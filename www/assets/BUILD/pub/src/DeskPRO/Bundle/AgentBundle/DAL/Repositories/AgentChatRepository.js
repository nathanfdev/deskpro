import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * AgentChatRepository
 */
export class AgentChatRepository extends ApiRepository {
  /**
   * @param entityId
   * @param type
   * @returns {*}
   */
  startChat(entityId, type) {
    return this.api.sendPost(`DP_API/${this.url}/start?follow_redirect`, {type: type, id: entityId});
  }

  /**
   * @param chatId
   * @param search
   * @param page
   * @param order
   * @returns {*}
   */
  loadMessages(chatId, search = '', page = 1, order = 'date_created') {
    return this.api.sendGet(`DP_API/${this.url}/${chatId}/messages?` + this.compileParams({search, page, order}));
  }

  /**
   * @param chatId
   * @param message
   * @param uuid
   * @returns {*}
   */
  addMessage(chatId, message, uuid) {
    return this.api.sendPost(`DP_API/${this.url}/${chatId}/messages`, {message, uuid});
  }

  /**
   * @returns {*}
   */
  loadMessagesCount() {
    return this.api.sendGet(`DP_API/${this.url}/messages/count`);
  }

  /**
   * @param ids
   * @param status
   * @returns {*}
   */
  markMessages(ids, status) {
    return this.api.sendPut(`DP_API/${this.url}/messages/mark`, {ids, status});
  }
}
