import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { loadBatch, setCollection, releaseCollection } from '../Actions/store'
import { async, asyncIndicator, composeHandlers } from 'Ampliflux/reducers/handlers';

const storeInitialState = {};

function handleSetCollection(state, {recordName, collectionName, records}) {
  // convert records to ID indexed map, convert record IDs to strings to properly work with Immutable .get()
  const recordsMap = {};
  for (const record of records) {
    recordsMap['' + record['id']] = record;
    recordsMap['' + record['id']]['id'] = '' + recordsMap['' + record['id']]['id'];
  }
  const recordIds = Object.keys(recordsMap);

  return state.mergeDeep({
    [recordName]: {
      records: recordsMap,
      collections: {[collectionName]: recordIds},
      statuses: {[collectionName]: {isLoaded: true}}
    }
  });
}

export default createReducer(storeInitialState, {
  [loadBatch]: composeHandlers(
    asyncIndicator((state, {recordName, collectionName, records}) => ({
      loading:   `${recordName}.statuses.${collectionName}.isLoading`,
      success:   `${recordName}.statuses.${collectionName}.isLoaded`,
      isError:   `${recordName}.statuses.${collectionName}.isError`,
      errorCode: `${recordName}.statuses.${collectionName}.errorCode`
    })),
    async({
      success: handleSetCollection
    })
  ),

  [setCollection]: handleSetCollection,

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

