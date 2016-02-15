import { createAction } from 'Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';


// loadAll(), loadBatch() and loadFromApi() have the same ID because they share reducer --------------------------------

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, ids, collectionName) => (dispatch, getState) => {
    const recordRepository = repository(recordName);

    const recordStore = getState().RecordsStore.store.get(recordName);
    const loaded = recordStore && recordStore.has('records')
                 ? recordStore.get('records').keySeq().toArray()
                 : [];

    const targets = [];
    ids.forEach(id => {
      if (loaded.indexOf(id) + loaded.indexOf('' + id) == -2) {
        targets.push(id);
      }
    });

    return {
      recordName,
      collectionName,
      promise: recordRepository.loadBatch(targets).then(response => ({
        recordName,
        collectionName,
        records: response.getData().data
      }))
    }
  }
);
export const loadAll = createAction(
  'RECORDS_STORE_LOAD',
  (recordName) => ({
    recordName,
    collectionName: 'all',
    promise: repository(recordName).loadAll().then(response => ({
      recordName,
      collectionName: 'all',
      records: response.getData().data
    }))
  })
);
export const loadFromApi = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, url, collectionName) => ({
    recordName,
    collectionName,
    promise: api.sendGet(url).then(response => ({
      recordName,
      collectionName,
      records: response.getData().data
    }))
  })
);

// ---------------------------------------------------------------------------------------------------------------------

export const setCollection = createAction(
  'RECORDS_STORE_SET_COLLECTION',
  (recordName, collectionName, records) => ({recordName, collectionName, records})
);

export const releaseCollection = createAction(
  'RECORDS_STORE_RELEASE_COLLECTION',
  (recordName, collectionName) => ({recordName, collectionName})
);
