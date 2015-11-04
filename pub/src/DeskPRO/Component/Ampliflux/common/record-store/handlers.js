import { async, asyncIndicator, composeHandlers } from '../../reducers/handlers';
import { MODE_SET } from './actions';
import Immutable from 'immutable';
import invariant from 'invariant';
import warning from 'warning';

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
  state.get('records').forEach((record, stringId) => {
    const intId = Number(stringId);
    if (valid.indexOf(intId) === -1) {
      next = next.set('records', next.get('records').delete(stringId));
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
    invariant(
      Immutable.Iterable.isIterable(state),
      'releaseRecords() state must be an Immutable instance. Got %s',
      state
    );

    const ids = payload.ids || state.getIn(['requests', payload.requestId]).toArray();
    const next = state.setIn(
      ['requests', payload.requestId],
      state.getIn(['requests', payload.requestId]).filter(recordId => ids.indexOf(recordId) === -1)
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
  return (state, payload) => {
    invariant(
      Immutable.Iterable.isIterable(state),
      'releaseRequest() state must be an Immutable instance, got %s',
      state
    );

    return gc(state.deleteIn(['requests', payload.requestId]));
  };
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

  const mergeState = Immutable.fromJS({
    records: state.get('records').merge(setRecords),
    requests: state.get('requests').merge({[requestId]: recordIds}),
    status: state.get('status').merge({[requestId]: {isDone: true}})
  });

  return state.merge(mergeState);
}


/**
 * (Reducer builder) Handles a 'set' request.
 *
 * @return {Function} reducer
 */
export function setRequestRecords() {
  return (state, payload) => {
    invariant(
      Immutable.Iterable.isIterable(state),
      'setRequestRecords() state must be an Immutable instance, got %s',
      state
    );

    return handleSetRequestRecords(
      state,
      payload.requestId,
      payload.records,
      payload.ids,
      payload.mode
    );
  };
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
      isError: `status.${payload.requestId}.isError`,
      errorCode: `status.${payload.requestId}.errorCode`
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
export function buildRecordStoreHandlers(actionTypes) {
  const { releaseRecordsAction, releaseRequestAction, setRequestRecordAction, requestRecordsAction } = actionTypes;

  return {
    [releaseRecordsAction]: releaseRecords(),
    [releaseRequestAction]: releaseRequest(),
    [setRequestRecordAction]: setRequestRecords(),
    [requestRecordsAction]: requestRecords()
  };
}
