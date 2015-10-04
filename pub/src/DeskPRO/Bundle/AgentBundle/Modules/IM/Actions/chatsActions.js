import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';

export const startChat = createAction(
  'IM_START_CHAT',
  (target_id, target_type = 'agent') => {
    "use strict";
    return new Promise(resolve => resolve({target_id: target_id, target_type: target_type}));
  }
);