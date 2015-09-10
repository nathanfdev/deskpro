import Immutable from 'immutable';

export function gcRecords() {
  return () => ({});
}

export function releaseRecords() {
  return (requestId, ids) => {
    return { requestId, ids: Immutable.Set(ids)};
  };
}

export function releaseRequest() {
  return (requestId) => {
    return { requestId };
  };
}

export function setRequestRecords() {
  return (requestId, setRecords, reqIds, mode = 'append') => {
    let records = setRecords;
    let ids     = reqIds;

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
      records: records,
      ids: ids,
      mode: mode
    };
  };
}

export function requestRecords(stateKey, loaderFn) {
  return (requestId, reqIds, mode = 'append') => (dispatch, getState) => {
    const ids = Immutable.Set(reqIds);
    const state      = stateLoaderFn(getState);
    const records    = state.get('records');
    const missingIds = ids.filter(id => !records.has(id + ''));

    return {
      requestId: requestId,
      ids: ids,
      missingIds: missingIds,
      promise: new Promise((resolve) => {
        if (missingIds.size) {
          const newRecords = loaderFn(missingIds);
          if (!Immutable.Map.isMap(newRecords)) {
            Immutable.fromJS(newRecords);
          }
          resolve({
            requestId: requestId,
            records: records.merge(newRecords),
            ids: ids,
            mode: mode
          });
        } else {
          resolve({
            requestId: requestId,
            records: records,
            ids: ids,
            mode: mode
          });
        }
      })
    };
  };
}
