import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { openChat } from './uiActions';
import { releaseChats, setChatsRequest } from '../RecordStores/Actions/chatsActions';

export const startChat = createAction(
  'IM_START_CHAT',
  (targetId, targetType = 'agent', chatId = null) => (dispatch, getState) => {
    dispatch(openChat(targetId, targetType));
    return new Promise(
      (resolve, reject) => {
        const store = getState().RecordStores.IM.chats;
        if (chatId && store.get('records').has(chatId)) {
          return resolve(store.get('records').get(chatId).toJS());
        }
        return IM.startChat(targetId, targetType)
          .success((response) => {
            const records = {};
            dispatch(releaseChats('recent', [response.data.id]));
            records[response.data.id] = response.data;
            dispatch(setChatsRequest('recent', records, [response.data.id]));
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);

