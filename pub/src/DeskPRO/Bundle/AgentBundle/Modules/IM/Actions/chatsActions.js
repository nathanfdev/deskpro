import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { openChat } from './uiActions';
import { loadRecentChats } from '../RecordStores/Actions/imChatsActions';

export const startChat = createAction(
  'IM_START_CHAT',
  (targetId, targetType = 'agent') => (dispatch) => {
    dispatch(openChat(targetId, targetType));
    return new Promise(
      (resolve, reject) => {
        const pr = IM.startChat(targetId, targetType)
          .success(response => resolve(response.data))
          .error(response => reject(response));
        return pr;
      }
    );
  }
);