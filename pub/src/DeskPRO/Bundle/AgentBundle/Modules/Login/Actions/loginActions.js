import { createAction } from 'Ampliflux';
import { loadMe } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Actions/meActions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const emailSetError = createAction('LOGIN_EMAIL_SET_ERROR');
export const passwordSetError = createAction('LOGIN_PASSWORD_SET_ERROR');
export const setHasAuth = createAction('LOGIN_SET_HAS_AUTH');

export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => dispatch => {
    dispatch(emailSetError(null));
    dispatch(passwordSetError(null));

    DpApi.sendPost('DP_API/get_session', params)
      .success(() => {
        dispatch(loadMe());
        dispatch(setHasAuth(true));
      })
      .error((response, http) => {
        const data = http.xhr.responseJSON;
        if (data.code === 'bad_credentials') {
          dispatch(passwordSetError('Looks like this isn\'t the correct password'));
        } else if (data.code === 'no_person') {
          dispatch(emailSetError('No such account was found'));
        }
      });
  }
);

export const logout = createAction('LOGOUT');
