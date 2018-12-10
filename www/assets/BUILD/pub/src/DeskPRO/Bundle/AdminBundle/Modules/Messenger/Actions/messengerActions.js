import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const getSettings = createAction(
  'MESSENGER_GET_SETTINGS_ACTION',
  brandId => api.sendGet(`DP_API/messenger/settings/${brandId}/setup`)
);
