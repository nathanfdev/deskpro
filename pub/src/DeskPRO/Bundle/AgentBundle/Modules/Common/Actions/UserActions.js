import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import Immutable from 'immutable';

window.Immutable = Immutable;

export const gcUsers        = createAction('GC_USERS',               recordStoreActions.gcRecords());
export const releaseUsers   = createAction('RELEASE_USERS',          recordStoreActions.releaseRecords());
export const releaseRequest = createAction('RELEASE_USERS_REQUEST',  recordStoreActions.releaseRequest());
export const setUserRequest = createAction('SET_USERS',              recordStoreActions.setRequestRecords());

export const loadUsers      = createAction('LOAD_USERS', recordStoreActions.requestRecords(['Common', 'users'], (missingIds) => {
  return new Promise((resolve, reject) => {
    DpApi.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','))
      .success(data => {
        resolve(Immutable.fromJS(mapKeyedFromArray(data.data, 'id')));
      }).error(res => {
        reject(res);
      });
  });
}));
