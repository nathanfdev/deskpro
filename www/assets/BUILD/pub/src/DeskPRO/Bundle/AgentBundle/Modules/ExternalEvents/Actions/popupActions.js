import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const webhookAction = createAction(
  'EXTERNAL_POPUP_WEBHOOK_ACTION',
  (url, method, data) => api.sendPost('DP_API/external_events/popup/webhook', data)
);

export const dismissAction = createAction(
  'EXTERNAL_POPUP_DISMISS_ACTION'
);

export const createTicketAction = createAction(
  'EXTERNAL_POPUP_CREATE_TICKET'
);
