import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params =>
    api.sendPost('DP_API/get_session', params)
);
export default login;
