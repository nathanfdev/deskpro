import { createAction } from 'Ampliflux';
import { loadOptions } from './dpWindowActions';
import { loadPhraseTranslations } from '../../Chat/Actions/chatActions';
import { widgetSessionCodeSelector } from '../Selectors/bootstrap';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const addSessionCode = (state, params = {}) => {
  return {...params, __sid: widgetSessionCodeSelector(state)};
};

export const getSession = createAction(
  'WIDGET_GET_SESSION',
  sessionCode => new Promise(resolve =>
    DpApi
      .sendPost('DP_API/auth/get_session', {session_code: sessionCode}, {...ajaxOptions})
      .success(response => {
        localStorage.setItem('dpWidget.sessionCode', response.session_code);
        resolve(response.session_code);
      })
  )
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => new Promise(resolve => {
    Promise.
      all([
        dispatch(getSession(localStorage.getItem('dpWidget.sessionCode'))),
        dispatch(loadOptions(window.DP_OPTIONS)),
        dispatch(loadPhraseTranslations())
      ])
      .then(response => resolve(response));
  })
);
