import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const createVoiceAsset = createAction(
  'VOICE_AGENT_CREATE_ASSET',
  data => api.sendPost('DP_API/voice_assets', data)
);

export const deleteVoiceAsset = createAction(
  'VOICE_DELETE_ASSET',
  id => api.sendDelete(`DP_API/voice_assets/${id}`)
);
