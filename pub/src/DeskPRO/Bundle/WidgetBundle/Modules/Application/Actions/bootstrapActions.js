import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { loadOptions } from './dpWindowActions';
import { loadOnlineAgents } from './agentActions';

export const loadPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => DpApi.sendGet('DP_SERVE_API/dp.php/user-lang-1.js')
);

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
