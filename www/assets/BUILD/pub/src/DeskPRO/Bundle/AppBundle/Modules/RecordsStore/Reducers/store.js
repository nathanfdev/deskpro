import Immutable from 'immutable';
import { async, asyncIndicator, composeHandlers } from 'Ampliflux/reducers/handlers';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import { createReducer } from 'Ampliflux';
import { loadBatch, setCollection, releaseCollection, addToCollection, removeFromCollection, updateCollection } from '../Actions/store';

const storeInitialState = {};

function gc(state, recordName) {
  const validRecordIds = [];
  state.getIn([recordName, 'collections']).forEach((collection) => {
    collection.forEach((id) => {
      if (validRecordIds.indexOf(id) === -1) {
        validRecordIds.push(id);
      }
    });
  });
  const validRecords = state.getIn([recordName, 'records']).filter((record, id) => validRecordIds.indexOf(id) > -1);
  return state.setIn([recordName, 'records'], validRecords);
}

function handleSetCollection(state, { recordName, collectionName, records, ids, noUpdates }) {
  if (noUpdates) return state;
  const newRecords = Immutable.Map.isMap(records) ? records : mapKeyedFromArray(records, 'id');

  // count ids BEFORE we will update records. So we just insert new records in records and replace collection ids
  const newIds = ids ? Immutable.Set(ids) : newRecords.keySeq().toSet();
  return state.withMutations((map) => {
    map.mergeIn([recordName, 'records'], newRecords);
    map.setIn([recordName, 'collections', collectionName], newIds);
    map.mergeIn([recordName, 'statuses', collectionName], { success: true, loading: false });

    if (collectionName !== 'all') {
      if (!map.getIn([recordName, 'statuses', 'all'])) {
        map.mergeIn([recordName, 'statuses', 'all'], { success: true, loading: false });
      }

      const oldIds = map.getIn([recordName, 'collections', 'all']) || Immutable.fromJS([]);
      map.setIn([recordName, 'collections', 'all'], newIds.union(oldIds));
    }
  });
}

function handleAddToCollection(state, { recordName, collectionName, records }) {
  const newRecords = Immutable.Map.isMap(records) ? records : mapKeyedFromArray(records, 'id');

  // count ids AFTER we will update records. So we just insert new records in records and replace collection ids
  // nope, BEFORE
  const oldIds = Immutable.Set(state.getIn([recordName, 'collections', collectionName]));
  const newIds = newRecords.keySeq().toSet().union(oldIds);
  return state.withMutations((map) => {
    map.mergeIn([recordName, 'records'], newRecords);
    map.setIn([recordName, 'collections', collectionName], newIds);
    map.mergeIn([recordName, 'statuses', collectionName], { success: true, loading: false });
  });
}

function handleUpdateCollection(state, { recordName, records, mergeType }) {
  let currentRecords = state.getIn([recordName, 'records']) || Immutable.fromJS({});
  currentRecords = currentRecords.withMutations((set) => {
    const newRecords = Immutable.Map.isMap(records) ? records : mapKeyedFromArray(records, 'id');
    newRecords.forEach((record, id) => {
      let newRecord = record;
      if (mergeType === 'merge') {
        const oldRecord = set.get(id);
        if (oldRecord) {
          newRecord = oldRecord.merge(newRecord);
        }
      }

      set.set(id, newRecord);
    });
  });

  return state.setIn([recordName, 'records'], currentRecords);
}

export default createReducer(storeInitialState, {
  [loadBatch]: composeHandlers(
    asyncIndicator((state, { recordName, collectionName }) => ({
      loading:   `${recordName}.statuses.${collectionName}.loading`,
      success:   `${recordName}.statuses.${collectionName}.success`,
      isError:   `${recordName}.statuses.${collectionName}.isError`,
      errorCode: `${recordName}.statuses.${collectionName}.errorCode`
    })),
    async({ success: handleSetCollection })
  ),

  [setCollection]: handleSetCollection,

  [addToCollection]:  handleAddToCollection,
  [updateCollection]: handleUpdateCollection,

  [removeFromCollection]: (state, { recordName, collectionName, ids }) => {
    let collection = state.getIn([recordName, 'collections', collectionName]);

    collection = collection.withMutations((set) => {
      for (const i of ids) {
        set.delete(i);
      }
    });

    const next = state.setIn([recordName, 'collections', collectionName], collection);
    return gc(next, recordName);
  },

  [releaseCollection]: (state, { recordName, collectionName }) => {
    let next = state;

    if (next.hasIn([recordName, 'statuses', collectionName])) {
      // remove collection status indicators
      next = next.deleteIn([recordName, 'statuses', collectionName]);

      // delete collection
      next = next.deleteIn([recordName, 'collections', collectionName]);

      if (state.getIn([recordName, 'records'])) {
        next = gc(next, recordName); // clean up records
      }
    }

    return next;
  }
});

