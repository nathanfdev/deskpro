import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadSettings = createAction(
  'VOICE_LOAD_SETTINGS',
  () => new Promise(resolve => api.sendGet('DP_API/voice_settings').success(response => resolve(response.data)))
);

export const updateSettings = createAction(
  'VOICE_UPDATE_SETTINGS',
  data => api.sendPut('DP_API/voice_settings', data)
);
