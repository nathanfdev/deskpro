import { createAction } from 'Ampliflux';
import { loadOptions } from './dpWindowActions';
import { loadChatPhraseTranslations } from '../../Chat/Actions/chatActions';
import { loadTicketDisplayFields } from '../../Ticket/Actions/ticketActions';
import { widgetSessionCodeSelector } from '../Selectors/bootstrap';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import PortalPhrases from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const addSessionCode = (state, params = {}) => {
  return {...params, __sid: widgetSessionCodeSelector(state)};
};

// Api actions
export const getSession = createAction(
  'WIDGET_GET_SESSION',
  () => new Promise(resolve =>
    DpApi
      .sendPost('DP_API/auth/get_session', {session_code: localStorage.getItem('dpWidget.sessionCode')}, {...ajaxOptions})
      .success(response => {
        localStorage.setItem('dpWidget.sessionCode', response.session_code);
        resolve(response);
      })
  )
);

export const getSettings = createAction(
  'WIDGET_GET_SETTINGS',
  () => new Promise(resolve =>
    DpApi
      .sendGet('DP_API/widget/settings', {...ajaxOptions})
      .success(response => resolve(response)))
);

export const loadPortalPhraseTranslations = createAction(
  'WIDGET_LOAD_PHRASE_TRANSLATIONS',
  () => DpApi
    .sendGet('DP_API/lang/widget-phrases.json', {...ajaxOptions})
    .success(response => {
      PortalPhrases.setPhrases(response);
    })
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => new Promise(resolve => {
    Promise.
      all([
        dispatch(getSession()),
        dispatch(getSettings()),
        dispatch(loadOptions(window.DP_OPTIONS)),
        dispatch(loadPortalPhraseTranslations()),
        dispatch(loadChatPhraseTranslations()),
        dispatch(loadTicketDisplayFields())
      ])
      .then(response => resolve(response));
  })
);
