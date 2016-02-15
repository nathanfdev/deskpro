import { createAction } from 'Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';

// loadAll(), loadBatch() and loadFromApi() have the same ID because they share reducer --------------------------------

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, ids, collectionName) => {
    const recordRepository = repository(recordName);

    return {
      recordName,
      collectionName,
      promise: recordRepository.loadBatch(ids).then(response => ({
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
