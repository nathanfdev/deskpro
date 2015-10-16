import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const emailChange = createAction('LOGIN_EMAIL_CHANGE');
export const passwordChange = createAction('LOGIN_PASSWORD_CHANGE');
export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  () => () => DpApi.sendPost('DP_API/login').success(() => window.location.href = '/agent')
);
export const logout = createAction('LOGOUT');

