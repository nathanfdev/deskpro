import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const generateNotifications = createAction(
  'DEV_GENERATE_NOTIFICATIONS',
  data => api.sendPost('DP_API/dev/gen_notifications', data)
);
