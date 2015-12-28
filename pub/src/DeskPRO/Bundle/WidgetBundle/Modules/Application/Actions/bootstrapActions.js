import { createAction } from 'Ampliflux';
import { loadOptions } from './dpWindowActions';
import { loadPhraseTranslations } from '../../Chat/Actions/chatActions';
import { widgetSessionCodeSelector } from '../Selectors/bootstrap';

export const ajaxOptions = {crossDomain: true, dataType: 'json'};
export const addSessionCode = (state, params = {}) => {
  return {...params, __sid: widgetSessionCodeSelector(state)};
};

export const setSessionCode = createAction(
  'WIDGET_SET_SESSION_CODE',
  sessionCode => {
    localStorage.setItem('dpWidget.sessionCode', sessionCode);
    return sessionCode;
  }
);

export const bootstrapWidget = createAction(
  'WIDGET_BOOTSTRAP',
  () => dispatch => new Promise(resolve => {
    Promise.
      all([
        dispatch(loadOptions(window.DP_OPTIONS)),
        dispatch(loadPhraseTranslations())
      ])
      .then(response => resolve(response));
  })
);
