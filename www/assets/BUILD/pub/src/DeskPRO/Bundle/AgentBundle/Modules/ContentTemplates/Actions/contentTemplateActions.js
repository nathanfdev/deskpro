import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadAll } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadContentTemplates = createAction(
  'AGENT_LOAD_CONTENT_TEMPLATES',
  (reload = false) => dispatch => dispatch(loadAll('ContentTemplate', reload))
);
