import Immutable from 'immutable';

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

  static markAllMessagesAsRead(state, payload) {
    const chat = MessagesHelper.getChat(state, payload.chatId);
    let newState = state;
    if (chat) {
      chat.messages.map((message) => {
        message.status = 2;
        newState = MessagesHelper.resetCounts(newState, payload);
        return true;
      });

      return newState.setIn(MessagesHelper.getPath(state, payload.chatId), { ...chat });
    }
    return newState;
  }

  static resetCounts(state, payload) {
    const counts = state.get('counts');
    if (counts.nested[payload.chatId] && payload.person !== state.get('me').id) {
      counts.count = counts.count > 0 ? counts.count - counts.nested[payload.chatId].count : 0;
      counts.nested[payload.chatId].count = 0;
      DeskPRO_Window.notifications.fireEvent('modCount', { count: counts.count }); // eslint-disable-line no-undef
      return state.set('counts', { ...counts });
    }
    return state;
  }

  static reduceCounts(state, payload) {
    const counts = state.get('counts');
    if (counts.nested[payload.chatId] && payload.status === 2 && payload.person !== state.get('me').id) {
      counts.nested[payload.chatId].count -= 1;
      counts.count = counts.count > 0 ? counts.count - 1 : 0;
      if (counts.nested[payload.chatId].count < 0) {
        counts.nested[payload.chatId].count = 0;
      }
      DeskPRO_Window.notifications.fireEvent('modCount', { count: counts.count }); // eslint-disable-line no-undef
      return state.set('counts', { ...counts });
    }
    return state;
  }

  static increaseCounts(state, payload) {
    const counts = state.get('counts');
    if (!counts.nested[payload.chatId]) {
      counts.nested[payload.chatId] = { count: 0 };
    }
    if (payload.status !== 2 && payload.person !== state.get('me').id) {
      counts.nested[payload.chatId].count += 1;
      counts.count += 1;
      DeskPRO_Window.notifications.fireEvent('modCount', { count: counts.count }); // eslint-disable-line no-undef
      return state.set('counts', { ...counts });
    }

    return state;
  }

  static markMessagesOptimistic(state, payload) {
    return MessagesHelper.markMessages(state.set('updatingMessages', true), payload);
  }

  static markAllMessagesOptimistic(state, payload) {
    return MessagesHelper.markAllMessagesAsRead(state.set('updatingMessages', true), payload);
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
    const { data } = payload.data;
    const chat = MessagesHelper.getChat(state, data.chat) || { messages: Immutable.fromJS({}) };
    chat.messages = chat.messages.set(data.uuid, data);
    const newState = MessagesHelper.increaseCounts(state, {
      chatId: data.chat,
      uuids:  [data.uuid],
      status: data.status,
      person: data.person
    });

    return newState.setIn(MessagesHelper.getPath(state, payload.data.data.chat), { ...chat });
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
