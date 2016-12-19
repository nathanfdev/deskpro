import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const createAsset = createAction(
  'VOICE_CREATE_ASSET',
  data => repository('VoiceAsset').create(data)
);

export const updateAsset = createAction(
  'VOICE_UPDATE_ASSET',
  (id, data) => repository('VoiceAsset').update(data, id)
);
