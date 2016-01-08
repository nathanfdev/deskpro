import { createAction } from 'Ampliflux';
import { loadMe } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Actions/meActions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const setHasAuth = createAction('LOGIN_SET_HAS_AUTH');
export const login = createAction(
  'LOGIN_SUBMIT_FORM',
  params => dispatch => {
    const promise = DpApi.sendPost('DP_API/get_session', params);
    promise.success(() => {
      dispatch(loadMe());
      dispatch(setHasAuth(true));
    });

    return promise;
  }
);

export const logout = createAction('LOGOUT');
