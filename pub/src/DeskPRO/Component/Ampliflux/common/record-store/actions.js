import objGet from 'lodash/object/get';
import Immutable from 'immutable';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

export const MODE_APPEND = 'append';
export const MODE_SET = 'set';

/**
 * (Action creator builder) Releases records for a request.
 *
 * @return {Function} action creator
 */
export function releaseRecords() {
  return (requestId, ids) => ({ requestId, ids });
}

/**
 * (Action creator builder) Releases an entire request.
 *
 * @return {Function} action creator
 */
export function releaseRequest() {
  return (requestId) => ({ requestId });
}

/**
 * (Action creator builder) Adds records to the store and registers interest for the request.
 *
 * @param {String} defaultMode Specify the default mode (MODE_APPEND or MODE_SET).
 * @return {Function} action creator
 */
export function setRequestRecords(defaultMode = MODE_APPEND) {
  return (requestId, setRecords, reqIds, mode = defaultMode) => {
    let records = setRecords;
    let ids = reqIds;

    if (!records) {
      records = Immutable.Map();
    }

    const collectIds = typeof ids === 'undefined' || ids === false || ids === null;
    if (collectIds) {
      ids = [];
    }

    if (!Immutable.Iterable.isIterable(records)) {
      records = Immutable.fromJS(records);
    }
    if (!Immutable.Map.isMap(records)) {
      records = mapKeyedFromArray(records, 'id');
    }

    if (collectIds) {
      const keys = [];
      for (const value of records.keys()) {
        keys.push(value);
      }

      ids = keys;
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

/**
 * (Action creator builder) Used to request new records from the store. If they aren't loaded yet,
 * they will loaded now.
 *
 * @param  {String}   stateKey The key in the store that is being used for the record-store. Use an array to denote hierarchy.
 * @param  {Function} loaderFn Your function will accept an Immutable.Set of IDs the reqestor wants to load.
 * @param  {String}   defaultMode Specify the default mode (MODE_APPEND or MODE_SET).
 * @return {Function} action creator
 */
export function requestRecords(stateKey, loaderFn, defaultMode = MODE_APPEND) {
  return (requestId, reqIds, mode = defaultMode) => (dispatch, getState) => {
    const ids = Immutable.Set(reqIds);
    const allState = getState();
    const state = objGet(allState, stateKey) || Immutable.fromJS({records: {}});
    const records = state.get('records');
    const missingIds = ids.filter(id => !records.has(id));

    return {
      requestId: requestId,
      ids: ids,
      missingIds: missingIds,
      promise: new Promise((resolve, reject) => {
        if (missingIds.size) {
          loaderFn(missingIds).then(newRecords => {
            resolve({
              requestId: requestId,
              records: records.merge(mapKeyedFromArray(newRecords, 'id')),
              ids: ids,
              mode: mode
            });
          }).catch(error => {
            reject(error);
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

/**
 * (Action creator builder) Used to create a records request within the store. If request isn't yet loaded/created,
 * it'll be fulfilled with records array resolved from loaderFn().
 *
 * @param {String}   stateKey    The key in the store that is being used for the record-store. Use an array to denote hierarchy.
 * @param {String}   requestId   Request ID used to store the loaded records within the record-sore.
 * @param {Function} loaderFn    Your function will accept an Immutable.Set of IDs the reqestor wants to load.
 * @param {String}   defaultMode Specify the default mode (MODE_APPEND or MODE_SET).
 * @return {Function} action creator
 */
export function createRecordsRequest(stateKey, requestId, loaderFn, defaultMode = MODE_APPEND) {
  return (mode = defaultMode) => (dispatch, getState) => {
    const state = objGet(getState(), stateKey) || Immutable.fromJS({records: {}, requests: []});
    const records = state.get('records');
    const requests = state.get('requests');

    return {
      requestId: requestId,
      promise: new Promise((resolve, reject) => {
        if (requests.has(requestId)) {
          resolve({
            requestId: requestId,
            records: records,
            ids: requests.get(requestId),
            mode: mode
          });
        } else {
          loaderFn().then(newRecords => {
            const merged = records.merge(mapKeyedFromArray(newRecords, 'id'));
            resolve({
              requestId: requestId,
              records: merged,
              ids: newRecords.map(record => record.id),
              mode: mode
            });
          }).catch(response => reject({
            requestId: requestId,
            mode: mode,
            response: response
          }));
        }
      })
    };
  };
}
