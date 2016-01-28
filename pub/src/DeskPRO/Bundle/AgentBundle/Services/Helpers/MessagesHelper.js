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
        return state.setIn(this.getPath(payload), {...chat});
      }
    }
    return state;
  }

  markMessagesOptimistic(state, payload) {
    return this.markMessages(state.set('updatingMessages', true), payload);
  }

  getChat(state, payload) {
    return state.getIn(this.getPath(payload));
  }

  getPath(payload) {
    return ['chatMessages', payload.chatId];
  }
}
