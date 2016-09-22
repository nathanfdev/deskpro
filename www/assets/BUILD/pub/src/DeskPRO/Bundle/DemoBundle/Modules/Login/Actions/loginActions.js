import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params =>
    api.sendPost('DP_API/get_session', params)
);
export const checkToken = createAction(
  'LOGIN_CHECK_TOKEN',
  () =>
    api.sendGet('DP_API/me')
);
export const forgotPassword = createAction(
  'LOGIN_FORGOT_PASSWORD',
  params =>
    api.sendPost('/login/reset-password/send.json', params)
);
