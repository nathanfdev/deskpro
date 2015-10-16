import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const login = createAction(
  'LOGIN',
  () => () => DpApi.sendPost('DP_API/login').success(() => window.location.href = '/agent')
);
export const logout = createAction('LOGOUT');

