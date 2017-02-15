import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const createVoiceAsset = createAction(
  'VOICE_CREATE_ASSET',
  data => api.sendPost('DP_API/voice_assets/create', data)
);
