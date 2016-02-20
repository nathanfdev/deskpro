import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { history } from '../../../Services/history';

export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => {
    const promise = api.sendPost('DP_API/get_session', params);
    promise.success(() => {
      promise.success(() => history.replace(`${DP_BASE_URL_RELATIVE}/${DP_AGENT_INTERFACE_PATH_NAMESPACE}/`));
    });

    return promise;
  }
);

export const logout = createAction('LOGOUT');
