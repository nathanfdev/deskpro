import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const emailSetError = createAction('LOGIN_EMAIL_SET_ERROR');
export const passwordSetError = createAction('LOGIN_PASSWORD_SET_ERROR');

export const emailChange = createAction(
  'LOGIN_EMAIL_CHANGE',
  value => dispatch => {
    dispatch(emailSetError(null));
    dispatch(passwordSetError(null));

    return value;
  }
);

export const passwordChange = createAction(
  'LOGIN_PASSWORD_CHANGE',
  value => dispatch => {
    dispatch(emailSetError(null));
    dispatch(passwordSetError(null));

    return value;
  }
);

export const toggleRememberMe = createAction('LOGIN_TOGGLE_REMEMBER_ME');
export const setToken = createAction('LOGIN_SET_TOKEN');
export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => dispatch => DpApi.sendPost('DP_API/api_tokens', params)
    .success(response => dispatch(setToken(response.data.token)))
);

export const logout = createAction('LOGOUT');
