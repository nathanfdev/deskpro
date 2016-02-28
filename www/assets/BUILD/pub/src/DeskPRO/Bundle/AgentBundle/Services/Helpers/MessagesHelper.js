export class MessagesHelper {

  markMessages(state, payload) {
    const chat = this.getChat(state, payload.chatId);
    let changed = false;
    if (chat) {
      payload.uuids.map(uuid => {
        if (chat.messages.has(uuid) && chat.messages.get(uuid).status < payload.status) {
          changed = true;
          chat.messages.get(uuid).status = payload.status;
        }
      });
      if (changed) {
        return state.setIn(this.getPath(state, payload.chatId), {...chat});
      }
    }
    return state;
  }

  markMessagesOptimistic(state, payload) {
    return this.markMessages(state.set('updatingMessages', true), payload);
  }

  addMessageOptimistic(state, payload) {
    const chat = this.getChat(state, payload.data.agent_chat_id);
    if (chat) {
      chat.messages = chat.messages.set(payload.data.uuid, payload.data);
      return state.setIn(this.getPath(state, payload.data.agent_chat_id), {...chat});
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
      case 'refresh_counts':
        handler = this.handleRefreshCounts;
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
    const chat = this.getChat(state, payload.data.agent_chat_id);
    if (chat) {
      chat.messages = chat.messages.set(payload.data.uuid, payload.data);
      return state.setIn(this.getPath(state, payload.data.agent_chat_id), {...chat});
    }

    return state;
  }

  handleMarkMessage(state, payload) {
    return this.markMessages(state, {
      chatId: payload.data.chat_id,
      uuids: [payload.data.message_uuid],
      status: payload.data.status
    });
  }

  handleRefreshCounts(state, payload) {
    return state.set('counts', payload.data);
  }

  idle(state) {
    return state;
  }
}
