import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const toggleOverlay = createAction('IM_TOGGLE_OVERLAY');

export const openChat = createAction('IM_OPEN_CHAT');

export const toggleGroupDrawer = createAction('IM_TOGGLE_GROUP_ADD_DRAWER');

export const markChatAsManuallyClosed = createAction(
  'MARK_CHAT_AS_CLOSED',
  chatId => chatId
);

export const closeChat = createAction(
  'IM_CLOSE_CHAT',
  (chatId = null) => (dispatch) => {
    if (chatId) {
      dispatch(markChatAsManuallyClosed(chatId));
    }
    return chatId;
  }
);

export const openGroupDrawer = createAction(
  'IM_OPEN_GROUP_ADD_DRAWER',
  payload => (dispatch) => {
    dispatch(closeChat());
    return payload;
  }
);

export const closeGroupDrawer = createAction('IM_CLOSE_GROUP_ADD_DRAWER');

export const startChat = createAction(
  'IM_START_CHAT',
  (targetParams, chatId = null, forced = false) => (dispatch, getState) => {
    dispatch(closeGroupDrawer);
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
          method = () => repository('AgentChat').load(chatId);
        } else {
          method = () => repository('AgentChat').startChat(targetParams.id, targetParams.type, targetParams.name || '');
        }
        return method()
          .success((response) => {
            const records = {};
            records[response.data.id] = response.data;
            dispatch(addToCollection('AgentChat', 'recent', records, [parseInt(response.data.id, 10)]));
            dispatch(markChatAsManuallyClosed(response.data.id));
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);
