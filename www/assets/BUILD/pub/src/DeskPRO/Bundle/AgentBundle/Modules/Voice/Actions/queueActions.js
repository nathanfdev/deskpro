import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadQueues = createAction(
  'VOICE_LOAD_QUEUES',
  () => dispatch => dispatch(loadAll('VoiceQueue'))
);
