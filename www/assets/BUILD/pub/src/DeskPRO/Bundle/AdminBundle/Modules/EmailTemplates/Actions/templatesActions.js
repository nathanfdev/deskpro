import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadTemplates = createAction(
  'EMAIL_TEMPLATES_LOAD_TEMPLATES',
  () => dispatch => dispatch(loadAll('EmailTemplates'))
);

