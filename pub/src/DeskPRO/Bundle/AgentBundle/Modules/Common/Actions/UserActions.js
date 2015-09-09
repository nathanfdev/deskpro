import { createAction } from "Ampliflux";
import { objectKeyedFromArray } from "DeskPRO/Component/Util/Objects";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import Immutable from 'immutable';

export const gcUsers        = createAction("GC_USERS");
export const releaseUsers   = createAction("RELEASE_USERS",   (requestId, userIds) => ({ requestId, userIds }));
export const releaseRequest = createAction("RELEASE_REQUEST", (requestId) => ({ requestId }));

export const setUserRequest = createAction("SET_USER_REQUEST", (requestId, records = {}, userIds = null, mode = 'append') => {
  if (!Immutable.Map.isMap(records)) {
    records = Immutable.fromJS(records);
  }

  if (userIds === null) {
    userIds = records.keys().toArray();
  }

  return {
    requestId: requestId,
    records:   records,
    ids:       userIds,
    mode:      mode
  }
});

export const loadUsers = createAction("LOAD_USERS", (requestId, userIds, mode = 'append') => (dispatch, getState) => {
  const users         = getState().Common.users;
  const missingIds    = userIds.filter(id => !users.get('records').has(id+""));

  return {
    requestId: requestId,
    promise: new Promise(function(resolve, reject) {
      if (missingIds.length) {
        DpApi.sendGet("DP_API/people?ids=" + missingIds.join(','))
          .success(data => {
            resolve({
              requestId: requestId,
              records: Immutable.fromJS(objectKeyedFromArray(data.data, 'id')),
              ids: userIds,
              mode: mode
            })
          })
          .error(res => reject(res));
      } else {
        // nothing new to load
        resolve({
          requestId: requestId,
          records: Immutable.Map(),
          ids: userIds,
          mode: mode
        });
      }
    })
  }
});
