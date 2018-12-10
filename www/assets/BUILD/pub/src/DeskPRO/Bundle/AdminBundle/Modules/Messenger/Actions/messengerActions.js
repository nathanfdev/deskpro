import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const getSettings = createAction(
  'MESSENGER_GET_SETTINGS_ACTION',
  brandId => api.sendGet(`DP_API/messenger/settings/${brandId}/setup`)
);

export const saveSettings = createAction(
  'MESSENGER_SAVE_SETTINGS_ACTION',
  (brandId, settings) => {
    const data = settings.toJS();
    if (data.chat.ticketDefaults) {
      data.chat.department = data.chat.ticketDefaults.department;
      delete data.chat.ticketDefaults;
    }
    api.sendPost(`DP_API/messenger/settings/${brandId}/setup`, data);
  }
);
