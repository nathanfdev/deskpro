import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const webhookAction = createAction(
  'EXTERNAL_POPUP_WEBHOOK_ACTION',
  (url, method, data) => api.sendPost('DP_API/external_events/popup/webhook', data)
);
