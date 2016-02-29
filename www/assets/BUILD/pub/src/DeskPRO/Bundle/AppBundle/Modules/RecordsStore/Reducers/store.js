import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { loadBatch, setCollection, releaseCollection, addToCollection} from '../Actions/store';
import { async, asyncIndicator, composeHandlers } from 'Ampliflux/reducers/handlers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

const storeInitialState = {};

function mergeRecords(state, recordName, records) {
  const currentRecords = state.getIn([recordName, 'records']);
  return currentRecords ? currentRecords.merge(records) : records;
}

function gc(state, recordName) {
  const validRecordIds = [];
  state.getIn([recordName, 'collections']).forEach(collection => {
    collection.forEach(id => {
      if (validRecordIds.indexOf(id) === -1) {
        validRecordIds.push(id);
      }
    });
  });
  const validRecords = state.getIn([recordName, 'records']).filter((record, id) => validRecordIds.indexOf(id) > -1);
  return state.setIn([recordName, 'records'], validRecords);
}

function handleSetCollection(state, {recordName, collectionName, records, ids, noUpdates}) {
  if (noUpdates) return state;
  let newRecords = records instanceof Immutable.Map ? records : mapKeyedFromArray(records, 'id');

  // count ids BEFORE we will update records. So we just insert new records in records and replace collection ids
  const newIds = ids ? ids : newRecords.keySeq().toArray();
  newRecords = mergeRecords(state, recordName, newRecords);

  return state.mergeDeep({
    [recordName]: {
      records: newRecords,
      collections: {[collectionName]: newIds},
      statuses: {[collectionName]: {success: true, loading: false}}
    }
  });
}

function handleAddToCollection(state, {recordName, collectionName, records}) {
  let newRecords = records instanceof Immutable.Map ? records : mapKeyedFromArray(records, 'id');
  // count ids AFTER we will update records. So we just insert new records in records and replace collection ids
  newRecords = mergeRecords(state, recordName, newRecords);
  const newIds = newRecords.keySeq().toArray();

  return state.mergeDeep({
    [recordName]: {
      records: newRecords,
      collections: {[collectionName]: newIds},
      statuses: {[collectionName]: {success: true, loading: false}}
    }
  });
}

export default createReducer(storeInitialState, {
  [loadBatch]: composeHandlers(
    asyncIndicator((state, {recordName, collectionName}) => ({
      loading: `${recordName}.statuses.${collectionName}.loading`,
      success: `${recordName}.statuses.${collectionName}.success`,
      isError: `${recordName}.statuses.${collectionName}.isError`,
      errorCode: `${recordName}.statuses.${collectionName}.errorCode`
    })),
    async({
      success: handleSetCollection
    })
  ),

  [setCollection]: handleSetCollection,

  [addToCollection]: handleAddToCollection,

  [releaseCollection]: (state, {recordName, collectionName}) => {
    let next = state;

    if (next.hasIn([recordName, 'statuses', collectionName])) {
      // remove collection status indicators
      next = next.deleteIn([recordName, 'statuses', collectionName]);

      // delete collection
      next = next.deleteIn([recordName, 'collections', collectionName]);

      next = gc(next, recordName); // clean up records
    }

    return next;
  }
});

