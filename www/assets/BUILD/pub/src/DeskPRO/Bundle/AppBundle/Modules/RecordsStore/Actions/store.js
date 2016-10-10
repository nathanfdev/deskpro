import { createAction } from 'Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';


// loadAll(), loadBatch() and loadFromApi() have the same ID because they share reducer --------------------------------

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, ids, collectionName) => (dispatch, getState) => {
    const numericIds = [];
    ids.forEach((id) => {
      const intId = parseInt(id, 10);
      if (intId) {
        numericIds.push(intId);
      }
    });

    const recordStore = getState().RecordsStore.store.get(recordName);
    const loaded = recordStore && recordStore.has('records')
      ? recordStore.get('records')
      : Immutable.fromJS({});

    const targets = [];

    numericIds.forEach((id) => {
      if (!loaded.has(id) && !loaded.has(id.toString())) {
        targets.push(id);
      }
    });

    // merge new ids with existing collection
    const existIds = recordStore && recordStore.hasIn(['collections', collectionName])
      ? recordStore.getIn(['collections', collectionName])
      : Immutable.fromJS([]);

    const allCollectionIds = existIds.merge(numericIds).toJS();

    let result;
    if (targets.length) {
      result = {
        recordName,
        collectionName,
        allCollectionIds,
        promise: repository(recordName).loadBatch(targets).then(response => ({
          recordName,
          collectionName,
          allCollectionIds,
          records: response.getData().data
        }))
      };
    } else {
      result = {
        recordName,
        collectionName,
        allCollectionIds,
        records: []
      };
    }

    return result;
  }
);
export const loadAll = createAction(
  'RECORDS_STORE_LOAD',
  recordName => (dispatch, getState) => {
    const recordStore = getState().RecordsStore.store.get(recordName);

    let result;
    if (!recordStore || !recordStore.hasIn(['statuses', 'all'])) {
      result = {
        recordName,
        collectionName: 'all',
        ids:            [],
        promise:        repository(recordName).loadAll().then((response) => {
          const records = response.getData().data;
          const ids = records.map(record => record.id);

          return {
            recordName,
            ids,
            records,
            collectionName: 'all'
          };
        })
      };
    } else {
      result = {
        noUpdates: true
      };
    }

    return result;
  }
);
export const loadFromApi = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, url, collectionName) => ({
    recordName,
    collectionName,
    promise: api.sendGet(url).then((response) => {
      const records = response.getData().data;
      const ids = records.map(record => record.id);

      return {
        recordName,
        collectionName,
        ids,
        records
      };
    })
  })
);

// ---------------------------------------------------------------------------------------------------------------------

export const setCollection = createAction(
  'RECORDS_STORE_SET_COLLECTION',
  (recordName, collectionName, records) => {
    const recordsArray = records.map ? records : Object.keys(records).map(k => records[k]);

    return { recordName, collectionName, records: recordsArray };
  }
);

export const updateCollection = createAction(
  'RECORDS_STORE_UPDATE_COLLECTION',
  (recordName, records, mergeType = 'replace') => {
    const recordsArray = records.map ? records : Object.keys(records).map(k => records[k]);

    return { recordName, records: recordsArray, mergeType };
  }
);

export const addToCollection = createAction(
  'RECORDS_STORE_ADD_TO_COLLECTION',
  (recordName, collectionName, records) => {
    const recordsArray = records.map ? records : Object.keys(records).map(k => records[k]);

    return { recordName, collectionName, records: recordsArray };
  }
);

export const removeFromCollection = createAction(
  'RECORDS_STORE_REMOVE_FROM_COLLECTION',
  (recordName, collectionName, ids) => ({ recordName, collectionName, ids })
);

export const releaseCollection = createAction(
  'RECORDS_STORE_RELEASE_COLLECTION',
  (recordName, collectionName) => ({ recordName, collectionName })
);
