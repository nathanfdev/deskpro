import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';

export const loadPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => DpApi.sendPost('DP_SERVE_API/dp.php/user-lang-1.js')
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => {
    dispatch(loadPhraseTranslations());
  }
);
