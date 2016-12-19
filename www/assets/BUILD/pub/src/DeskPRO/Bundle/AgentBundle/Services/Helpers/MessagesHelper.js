export class MessagesHelper {

  markMessages(state, payload) {
    const chat = this.getChat(state, payload.chatId);
    let changed = false;
    let newState = state;
    if (chat) {
      payload.uuids.map(uuid => {
        if (chat.messages.has(uuid) && chat.messages.get(uuid).status < payload.status) {
          changed = true;
          chat.messages.get(uuid).status = payload.status;
          newState = this.reduceCounts(newState, payload);
        }
      });
      if (changed) {
        return newState.setIn(this.getPath(state, payload.chatId), { ...chat });
      }
    }
    return newState;
  }

  reduceCounts(state, payload) {
    const counts = state.get('counts');
    const nested = counts.nested;
    if (nested[payload.chatId] && payload.status === 2 && payload.person !== state.get('me').id) {
      nested[payload.chatId].count--;
      if (nested[payload.chatId].count < 0) nested[payload.chatId].count = 0;
      counts.nested = nested;
      return state.set('counts', { ...counts });
    }
    return state;
  }

  increaseCounts(state, payload) {
    const counts = state.get('counts');
    const nested = counts.nested;
    if (!nested[payload.chatId]) {
      nested[payload.chatId] = { count: 0 };
    }
    if (payload.status !== 2 && payload.person !== state.get('me').id) {
      nested[payload.chatId].count++;
      counts.nested = nested;
      return state.set('counts', { ...counts });
    }

    return state;
  }

  markMessagesOptimistic(state, payload) {
    return this.markMessages(state.set('updatingMessages', true), payload);
  }

  addMessageOptimistic(state, payload) {
    const chat = this.getChat(state, payload.data.chat);
    if (chat) {
      chat.messages = chat.messages.set(payload.data.uuid, payload.data);
      return state.setIn(this.getPath(state, payload.data.chat), { ...chat });
    }
    return state;
  }

  getChat(state, chatId) {
    return state.getIn(this.getPath(state, chatId));
  }

  getPath(state, chatId) {
    let firstKey = 'chatMessages';
    if (state.get('searching')) {
      firstKey = 'searchMessages';
    }
    return [firstKey, chatId];
  }

  handleActionAlerts(state, payload) {
    let handler;
    switch (payload.type) {
      case 'notification.agent_chat.new_message':
        handler = this.handleNewMessage.bind(this);
        break;
      case 'notification.agent_chat.mark_message':
        handler = this.handleMarkMessage.bind(this);
        break;
      default:
        handler = this.idle;
    }

    return handler(state, payload);
  }

  handleNewMessage(state, payload) {
    let newState = state;
    const { data } = payload.data;
    const chat = this.getChat(state, data.chat);
    if (chat) {
      chat.messages = chat.messages.set(data.uuid, data);
      newState = this.increaseCounts(state, {
        chatId: data.chat,
        uuids:  [data.uuid],
        status: data.status,
        person: data.person
      });
      return newState.setIn(this.getPath(state, payload.data.data.chat), { ...chat });
    }

    return newState;
  }

  handleMarkMessage(state, payload) {
    return this.markMessages(state, {
      chatId: payload.data.chat,
      uuids:  [payload.data.message_uuid],
      status: payload.data.status
    });
  }

  idle(state) {
    return state;
  }
}
