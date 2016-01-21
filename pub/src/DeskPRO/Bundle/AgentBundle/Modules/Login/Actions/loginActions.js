import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { history } from '../../../Services/history';

export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => {
    const promise = DpApi.sendPost('DP_API/get_session', params);
    promise.success(() => history.replace(`${DP_BASE_URL_RELATIVE}/agent/`));

    return promise;
  }
);

export const logout = createAction('LOGOUT');
