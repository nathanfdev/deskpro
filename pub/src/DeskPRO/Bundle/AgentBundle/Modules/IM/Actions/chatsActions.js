import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { openChat } from './uiActions';

export const startChat = createAction(
  'IM_START_CHAT',
  (targetId, targetType = 'agent') => (dispatch) => {
    dispatch(openChat());
    return new Promise(
      (resolve, reject) =>
        IM.startChat(targetId, targetType)
          .success(response => resolve(response.data))
          .error(response => reject(response))
    );
  }
);