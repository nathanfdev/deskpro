export class MessagesHelper {

  static markMessages(state, payload) {
    const chat = MessagesHelper.getChat(state, payload.chatId);
    let changed = false;
    let newState = state;
    if (chat) {
      payload.uuids.map((uuid) => {
        if (chat.messages.has(uuid) && chat.messages.get(uuid).status < payload.status) {
          changed = true;
          chat.messages.get(uuid).status = payload.status;
          newState = MessagesHelper.reduceCounts(newState, payload);
        }

        return true;
      });
      if (changed) {
        return newState.setIn(MessagesHelper.getPath(state, payload.chatId), { ...chat });
      }
    }
    return newState;
  }

  static reduceCounts(state, payload) {
    const counts = state.get('counts');
    const nested = counts.get('nested');
    if (nested.has(payload.chatId) && payload.status === 2 && payload.person !== state.get('me').id) {
      let count = nested.getIn([payload.chatId, 'count']) - 1;
      if (count < 0) count = 0;
      nested.setIn([payload.chatId, 'count'], (count));
      counts.set('nested', nested);
      return state.set('counts', { ...counts });
    }
    return state;
  }

  static increaseCounts(state, payload) {
    const counts = state.get('counts');
    const nested = counts.get('nested');
    if (!nested.has(payload.chatId)) {
      nested.set(payload.chatId, { count: 0 });
    }
    if (payload.status !== 2 && payload.person !== state.get('me').id) {
      const count = nested.getIn([payload.chatId, 'count']) + 1;
      nested.setIn([payload.chatId, 'count'], (count));
      counts.set('nested', nested);
      return state.set('counts', { ...counts });
    }
    return state;
  }

  static markMessagesOptimistic(state, payload) {
    return MessagesHelper.markMessages(state.set('updatingMessages', true), payload);
  }

  static addMessageOptimistic(state, payload) {
    const chat = MessagesHelper.getChat(state, payload.data.chat);
    if (chat) {
      chat.messages = chat.messages.set(payload.data.uuid, payload.data);
      return state.setIn(MessagesHelper.getPath(state, payload.data.chat), { ...chat });
    }
    return state;
  }

  static getChat(state, chatId) {
    return state.getIn(MessagesHelper.getPath(state, chatId));
  }

  static getPath(state, chatId) {
    let firstKey = 'chatMessages';
    if (state.get('searching')) {
      firstKey = 'searchMessages';
    }
    return [firstKey, chatId];
  }

  static handleActionAlerts(state, payload) {
    let handler;
    switch (payload.type) {
      case 'notification.agent_chat.new_message':
        handler = MessagesHelper.handleNewMessage;
        break;
      case 'notification.agent_chat.mark_message':
        handler = MessagesHelper.handleMarkMessage;
        break;
      default:
        handler = MessagesHelper.idle;
    }

    return handler(state, payload);
  }

  static handleNewMessage(state, payload) {
    let newState = state;
    const { data } = payload.data;
    const chat = MessagesHelper.getChat(state, data.chat);
    if (chat) {
      chat.messages = chat.messages.set(data.uuid, data);
      newState = MessagesHelper.increaseCounts(state, {
        chatId: data.chat,
        uuids:  [data.uuid],
        status: data.status,
        person: data.person
      });
      return newState.setIn(MessagesHelper.getPath(state, payload.data.data.chat), { ...chat });
    }

    return newState;
  }

  static handleMarkMessage(state, payload) {
    return MessagesHelper.markMessages(state, {
      chatId: payload.data.chat,
      uuids:  [payload.data.message_uuid],
      status: payload.data.status
    });
  }

  static idle(state) {
    return state;
  }
}

export default MessagesHelper;
