import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';

export const startChat = createAction(
  'IM_START_CHAT',
  (target_id, target_type = 'agent') => (dispatch) => {
    "use strict";
    return new Promise(
      (resolve, reject) =>
        IM.startChat(target_id, target_type)
          .success(response => resolve(response.data))
          .error(response => reject(response))
    );

  }
);