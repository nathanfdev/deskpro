import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { setCollection, releaseCollection } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

export const toggleOverlay = createAction('IM_TOGGLE_OVERLAY');

export const openChat = createAction('IM_OPEN_CHAT');

export const markChatAsManuallyClosed = createAction(
  'MARK_CHAT_AS_CLOSED',
  (chatId) => chatId
);

export const closeChat = createAction(
  'IM_CLOSE_CHAT',
  (chatId) => (dispatch) => {
    dispatch(markChatAsManuallyClosed(chatId));
    return chatId;
  }
);

export const startChat = createAction(
  'IM_START_CHAT',
  (targetId, targetType = 'agent', chatId = null, forced = false) => (dispatch, getState) => {
    if (!(forced && getState().IM.chats.getIn(['manuallyClosed', chatId]))) {
      dispatch(openChat());
    }
    return new Promise(
      (resolve, reject) => {
        const store = getState().RecordsStore.store.get('AgentChat');
        if (chatId && store.get('records').toJS()[chatId]) {
          return resolve(store.get('records').toJS()[chatId]);
        }
        let method;
        if (chatId) {
          method = IM.loadChat.bind(null, chatId);
        } else {
          method = IM.startChat.bind(null, targetId, targetType);
        }
        return method.call()
          .success((response) => {
            const records = {};
            dispatch(releaseCollection('UserChat', 'recent', [response.data.id]));
            records[response.data.id] = response.data;
            dispatch(setCollection('UserChat', 'recent', records, [parseInt(response.data.id, 10)]));
            dispatch(markChatAsManuallyClosed(response.data.id));
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);


