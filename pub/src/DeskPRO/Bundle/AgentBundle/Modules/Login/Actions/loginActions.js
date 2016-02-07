import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => {
    const promise = api.sendPost('DP_API/get_session', params);
    promise.success(() => {
      location.pathname = `${DP_BASE_URL_RELATIVE}/agent/tasks`;
    });

    return promise;
  }
);

export const logout = createAction('LOGOUT');
