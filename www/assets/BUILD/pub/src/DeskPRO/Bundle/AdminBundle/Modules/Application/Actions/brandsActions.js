import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadBrands = createAction(
  'VOICE_LOAD_ACCOUNTS',
  (reload = false) => dispatch => dispatch(loadAll('Brand', reload))
);
