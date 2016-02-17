import { createAction } from 'Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';


// loadAll(), loadBatch() and loadFromApi() have the same ID because they share reducer --------------------------------

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, ids, collectionName) => (dispatch, getState) => {
    const recordStore = getState().RecordsStore.store.get(recordName);
    const loaded = recordStore && recordStore.has('records')
                 ? recordStore.get('records')
                 : Immutable.fromJS({});

    const targets = [];

    ids.forEach(id => {
      if (!loaded.has(id) && !loaded.has('' + id)) {
        targets.push(id);
      }
    });

    let result;
    if (targets.length) {
      result = {
        recordName,
        collectionName,
        ids,
        promise: repository(recordName).loadBatch(targets).then(response => ({
          recordName,
          collectionName,
          ids,
          records: response.getData().data
        }))
      }
    } else {
      result = {
        recordName,
        collectionName,
        ids,
        records: []
      }
    }

    return result;
  }
);
export const loadAll = createAction(
  'RECORDS_STORE_LOAD',
  (recordName) => (dispatch, getState) => {
    const recordStore = getState().RecordsStore.store.get(recordName);

    let result;
    if (!recordStore || !recordStore.hasIn(['statuses', 'all'])) {
      result = {
        recordName,
        collectionName: 'all',
        ids: [],
        promise: repository(recordName).loadAll().then(response => {
          const records = response.getData().data;
          const ids = records.map(record => record.id);

          return {
            recordName,
            ids,
            records,
            collectionName: 'all'
          }
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
    promise: api.sendGet(url).then(response => {
      const records = response.getData().data;
      const ids = records.map(record => record.id);

      return {
        recordName,
        collectionName,
        ids,
        records
      }
    })
  })
);

// ---------------------------------------------------------------------------------------------------------------------

export const setCollection = createAction(
  'RECORDS_STORE_SET_COLLECTION',
  (recordName, collectionName, records) => {
    const recordsArray = records.map ? records : Object.keys(records).map(k => records[k]);

    return {recordName, collectionName, records: recordsArray, ids: recordsArray.map(record => record.id)};
  }
);

export const releaseCollection = createAction(
  'RECORDS_STORE_RELEASE_COLLECTION',
  (recordName, collectionName) => ({recordName, collectionName})
);
