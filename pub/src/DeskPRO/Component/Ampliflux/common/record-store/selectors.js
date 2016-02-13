import { createSelector } from 'reselect';
import Immutable from 'immutable';
import invariant from 'invariant';

/**
 * Creates a selector for the 'status' value on your store.
 *
 * @param {selector} storeSelector Your store selector
 * @return {selector} Selector for 'status'
 */
function createStoreStatusSel(storeSelector) {
  return createSelector(
    [storeSelector],
    store => store.get('status')
  );
}


/**
 * Creates a selector for the 'records' value on your store.
 *
 * @param {selector} storeSelector Your store selector
 * @return {selector} Selector for 'records'
 */
function createStoreRecordsSel(storeSelector) {
  return createSelector(
    [storeSelector],
    store => store.get('records')
  );
}


/**
 * Creates a selector for the 'requests' value.
 *
 * @param {selector} storeSelector Your store selector
 * @return {selector} Selector for 'requests'
 */
function createStoreRequestsSel(storeSelector) {
  return createSelector(
    [storeSelector],
    store => store.get('requests')
  );
}


/**
 * Creates a builder that can make a selector that gets the status
 * for a given request ID.
 *
 * @param {selector} statusSelector Your record-store's status selector
 * @return {Function} Builder for a requests status
 */
function createStatusSelBuilder(statusSelector) {
  return (requestId) => createSelector(
    [statusSelector],
    status => Immutable.Map({
      isLoading: status.getIn([requestId, 'isLoading']),
      isDone: status.getIn([requestId, 'isDone']),
      isError: status.getIn([requestId, 'isError']),
      errorCode: status.getIn([requestId, 'errorCode'])
    })
  );
}


/**
 * Creates a builder that can make a selector that gets the record ids
 * of a given request.
 *
 * @param {selector} requestsSelector Your record-store's request selector
 * @return {Function} Builder for a requests ids
 */
function createIdsSelBuilder(requestsSelector) {
  return (requestId) => createSelector(
    [requestsSelector],
    requests => requests.get(requestId, Immutable.Set())
  );
}


/**
 * Creates a builder that can make a selector that gets the record ids
 * of a given request.
 *
 * @param {selector} recordsSelector Your record-store's records selector
 * @return {Function} Builder for a requests records
 */
function createRecordsSelBuilder(recordsSelector) {
  return (requestIdsSelector) => createSelector(
    [recordsSelector, requestIdsSelector],
    (records, ids) => records.filter(r => ids.includes(r.get('id')))
  );
}


/**
 * Creates builders that create selectors that can be used by an actual request (e.g., inside a component).
 *
 * @param {Object} selectors  The object returned from createStoreSelectors
 * @return {Object} A map of selector creators
 */
export function createRequestSelectorsBuilder({ statusSel, recordsSel, requestsSel }) {
  invariant(typeof statusSel === 'function', 'statusSel must be a function, got %s', statusSel);
  invariant(typeof recordsSel === 'function', 'recordsSel must be a function, got %s', recordsSel);
  invariant(typeof requestsSel === 'function', 'requestsSel must be a function, got %s', requestsSel);

  return (requestId) => {
    const idsSel = createIdsSelBuilder(requestsSel)(requestId);
    return {
      statusSel: createStatusSelBuilder(statusSel)(requestId),
      idsSel: idsSel,
      recordsSel: createRecordsSelBuilder(recordsSel)(idsSel)
    };
  };
}


/**
 * Creates the common selectors for a given record store.
 *
 * @param {function} storeSel The main, top-level selector which should select your record store from the main store.
 * @return {Object} A map of selectors
 */
export function createStoreSelectors(storeSel) {
  function storeSelWrapper(base) {
    const store = storeSel(base);
    invariant(
      store,
      'Record store reducer must exist. Check path and make sure reducer file exists and loaded. Selector: %s',
      storeSel
    );

    return store;
  }

  return {
    statusSel: createStoreStatusSel(storeSelWrapper),
    recordsSel: createStoreRecordsSel(storeSelWrapper),
    requestsSel: createStoreRequestsSel(storeSelWrapper)
  };
}
