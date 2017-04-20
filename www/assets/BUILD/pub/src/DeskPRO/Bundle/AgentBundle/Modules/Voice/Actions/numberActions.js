import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadNumbers = createAction(
  'VOICE_LOAD_NUMBERS',
  () => dispatch => dispatch(loadAll('VoiceNumber'))
);
