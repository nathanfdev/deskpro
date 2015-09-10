import { createAction } from "Ampliflux";
import * as commonStoreActions from "Ampliflux/actions/commonStoreActions";
import { objectKeyedFromArray } from "DeskPRO/Component/Util/Objects";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import Immutable from 'immutable';

window.Immutable = Immutable;

export const gcUsers        = createAction("GC_USERS",         commonStoreActions.gcRecords());
export const releaseUsers   = createAction("RELEASE_USERS",    commonStoreActions.releaseRecords());
export const releaseRequest = createAction("RELEASE_REQUEST",  commonStoreActions.releaseRequest());
export const setUserRequest = createAction("SET_USER_REQUEST", commonStoreActions.setRequestRecords());

export const loadUsers = createAction("LOAD_USERS", commonStoreActions.requestRecords("users", () => {
  return new Promise((resolve, reject) => {
    DpApi.sendGet("DP_API/people?ids=" + missingIds.join(','))
      .success(data => {
        resolve(Immutable.fromJS(objectKeyedFromArray(data.data, 'id')));
      }).error(res => {
        reject(res);
      });
  });
}));
