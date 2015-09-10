import { createAction } from "Ampliflux";
import { objectKeyedFromArray } from "DeskPRO/Component/Util/Objects";
import isArray from "lodash/lang/isArray";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import Immutable from 'immutable';

export const gcRecords = () => {
  return {};
}

export const releaseRecords = () => (requestId, ids) => {
  ids = Immutable.Set(ids);
  return {requestId,ids};
}

export const releaseRequest = () => (requestId) => {
  return { requestId };
}

export const setRequestRecords = () => (requestId, records, ids, mode = 'append') => {
  if (!records) {
    records = Immutable.Map();
  }

  const collectIds = typeof ids === 'undefined' || ids === false || ids === null;
  if (collectIds) {
    ids = [];
  }

  if (!Immutable.Map.isMap(records)) {
    records = Immutable.fromJS(records);
  } else {
    if (collectIds) {
      ids = records.keys().toArray();
    }
  }

  ids = Immutable.Set(ids);

  return {
    requestId: requestId,
    records:   records,
    ids:       ids,
    mode:      mode
  }
}

export const requestRecords = (stateKey, loaderFn) => (requestId, ids, mode = 'append') => (dispatch, getState) => {
  ids = Immutable.Set(ids);
  const state      = stateLoaderFn(getState);
  const records    = state.get('records');
  const missingIds = ids.filter(id => !records.has(id+""));

  return {
    requestId: requestId,
    ids: ids,
    missingIds: missingIds,
    promise: new Promise((resolve, reject) => {
      if (missingIds.size) {
        let newRecords = loaderFn(missingIds);
        if (!Immutable.Map.isMap(newRecords)) {
          Immutable.fromJS(newRecords);
        }
        resolve({
          requestId: requestId,
          records:   records.merge(newRecords),
          ids:       ids,
          mode:      mode
        })
      } else {
        resolve({
          requestId: requestId,
          records:   records,
          ids:       ids,
          mode:      mode
        });
      }
    })
  }
}
