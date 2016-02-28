import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { loadBatch, setCollection, releaseCollection, updateCollection} from '../Actions/store';
import { async, asyncIndicator, composeHandlers } from 'Ampliflux/reducers/handlers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

const storeInitialState = {};

function handleSetCollection(state, {recordName, collectionName, records, ids, noUpdates}) {
  if (noUpdates) {
    return state;
  }

  let newRecords = records instanceof Immutable.Map ? records : mapKeyedFromArray(records, 'id');
  let newIds;
  if (!ids) {
    newIds = newRecords.keySeq().toArray();
  } else {
    newIds = ids;
  }

  // records sohuld be merged, while collection should be overriden
  const currentRecords = state.getIn([recordName, 'records']);
  if (currentRecords) newRecords = newRecords.mergeDeep(currentRecords);

  return state.mergeDeep({
    [recordName]: {
      records: newRecords,
      collections: {[collectionName]: newIds},
      statuses: {[collectionName]: {success: true, loading: false}}
    }
  });
}

function handleUpdateCollection(state, {recordName, collectionName, records}) {
  let newRecords = records instanceof Immutable.Map ? records : mapKeyedFromArray(records, 'id');
  const currentRecords = state.getIn([recordName, 'records']);
  newRecords = currentRecords.merge(newRecords);

  return handleSetCollection(state, {recordName: recordName, collectionName: collectionName, records: newRecords});
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

  [updateCollection]: handleUpdateCollection,

  [releaseCollection]: (state, {recordName, collectionName}) => {
    let next = state;

    if (next.hasIn([recordName, 'statuses', collectionName])) {
      // remove collection status indicators
      next = next.deleteIn([recordName, 'statuses', collectionName]);

      // delete collection
      next = next.deleteIn([recordName, 'collections', collectionName]);

      // clean up records
      const validRecordIds = [];
      next.getIn([recordName, 'collections']).forEach(collection => {
        collection.forEach(id => {
          if (validRecordIds.indexOf(id) === -1) {
            validRecordIds.push(id);
          }
        });
      });
      const validRecords = state.getIn([recordName, 'records']).filter((record, id) => validRecordIds.indexOf(id) > -1);
      next = next.setIn([recordName, 'records'], validRecords);
    }

    return next;
  }
});

