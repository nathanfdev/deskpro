import { createAction } from 'Ampliflux';
import { loadOptions } from './dpWindowActions';
import { loadOnlineAgents } from './agentActions';
import { loadPhraseTranslations } from '../../Chat/Actions/chatActions';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => new Promise(resolve => {
    Promise.
      all([
        dispatch(loadOptions(window.DP_OPTIONS)),
        dispatch(loadPhraseTranslations()),
        dispatch(loadOnlineAgents())
      ])
      .then(response => resolve(response));
  })
);
