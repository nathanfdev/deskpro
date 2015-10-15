import { async, asyncIndicator, composeHandlers } from '../../reducers/handlers';
import { MODE_SET } from './actions';

/**
 * (Reducer builder) Runs cleanup of unused records
 *
 * @param {Immutable.Map} state The current state
 * @return {Function} reducer
 */
function gc(state) {
  const valid = [];
  state.get('requests').map(records => valid.push(...records));

  let next = state;
  state.get('records').forEach((record, recordId) => {
    if (valid.indexOf(recordId) === -1) {
      next = next.deleteIn(['records', recordId]);
    }
  });

  return next;
}


/**
 * (Reducer builder) Unregisters 'interest' in certain records.
 * When GC is run, any records in the store that are not currently being
 * used by any request will be cleaned up.
 *
 * @return {Function} reducer
 */
export function releaseRecords() {
  return (state, payload) => {
    const next = state.setIn(
      ['requests', payload.requestId],
      state.getIn(['requests', payload.requestId]).filter(recordId => payload.ids.indexOf(recordId) === -1)
    );

    return gc(next);
  };
}


/**
 * Builds a reducer that unregisters an entire request.
 *
 * @return {Function} reducer
 */
export function releaseRequest() {
  return (state, payload) => gc(state.deleteIn(['requests', payload.requestId]));
}


/**
 * (Reducer builder) Handles updating the store with updated requests.
 *
 * @param {Immutable.Map} state The current state
 * @param {String} requestId The request ID
 * @param {Immutable.Map} setRecords New records to merge into the collection
 * @param {Immutable.Set|int[]} ids The ids the requestor is actually interested in (i.e., ids of setRecords + ids of records not provided that are already in state)
 * @param {String} mode 'set' or 'append'. If 'set', then the requestors 'ids' will be set to `ids`. Otherwise, they are added to the current set of IDs.
 * @return {Immutable.Map} New state
 */
function handleSetRequestRecords(state, requestId, setRecords, ids, mode) {
  let recordIds = Immutable.Set(ids || []);

  if (mode !== MODE_SET) {
    const existRecordIds = state.getIn(['requests', requestId]);
    if (existRecordIds) {
      recordIds = recordIds.merge(existRecordIds);
    }
  }

  return state.merge({
    records: state.get('records').merge(setRecords),
    requests: { [requestId]: recordIds}
  });
}


/**
 * (Reducer builder) Handles a 'set' request.
 *
 * @return {Function} reducer
 */
export function setRequestRecords() {
  return (state, payload) => handleSetRequestRecords(
    state,
    payload.requestId,
    payload.records,
    payload.ids,
    payload.mode
  );
}


/**
 * (Reducer builder) Handles a 'load' request.
 *
 * @return {Function} reducer
 */
export function requestRecords() {
  return composeHandlers(
    asyncIndicator((state, payload) => ({
      loading: `status.${payload.requestId}.isLoading`,
      success: `status.${payload.requestId}.isDone`,
    })),
    async({
      success: (state, payload) => {
        return handleSetRequestRecords(
          state,
          payload.requestId,
          payload.records,
          payload.ids,
          payload.mode
        );
      }
    })
  );
}


/**
 * Creates empty state required for common storage.
 *
 * @return {Object} empty state
 */
export function createEmptyRecordStoreState() {
  return {
    records: {},
    requests: {},
    status: {}
  };
}


/**
 * Builds a map of reducers that handle all of the relevant actions for common storage.
 *
 * @param {Object} actionTypes A map of type => action type constant
 * @return {Object} Handlers map
 */
export function buildRecordStoreHandlers({ releaseRecordsAction, releaseRequestAction, setRequestRecordAction, requestRecordsAction }) {
  return {
    [releaseRecordsAction]: releaseRecords(),
    [releaseRequestAction]: releaseRequest(),
    [setRequestRecordAction]: setRequestRecords(),
    [requestRecordsAction]: requestRecords()
  };
}
