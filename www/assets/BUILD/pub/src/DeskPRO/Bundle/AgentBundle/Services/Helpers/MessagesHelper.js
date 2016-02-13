export class MessagesHelper {

  markMessages(state, payload) {
    const chat = this.getChat(state, payload);
    let changed = false;
    if (chat) {
      payload.uuids.map(uuid => {
        if (chat.messages.has(uuid) && chat.messages.get(uuid).status < payload.status) {
          changed = true;
          chat.messages.get(uuid).status = payload.status;
        }
      });
      if (changed) {
        return state.setIn(this.getPath(state, payload), {...chat});
      }
    }
    return state;
  }

  markMessagesOptimistic(state, payload) {
    return this.markMessages(state.set('updatingMessages', true), payload);
  }

  addMessageOptimistic(state, payload) {
    const transformed = {chatId: payload.data.agent_chat_id};
    const chat = this.getChat(state, transformed);
    if (chat) {
      chat.messages = chat.messages.set(payload.data.uuid, payload.data);
      return state.setIn(this.getPath(state, transformed), {...chat});
    }
    return state;
  }

  getChat(state, payload) {
    return state.getIn(this.getPath(state, payload));
  }

  getPath(state, payload) {
    let firstKey = 'chatMessages';
    if (state.get('searching')) {
      firstKey = 'searchMessages';
    }
    return [firstKey, payload.chatId];
  }
}
