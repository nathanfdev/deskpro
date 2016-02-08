import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, target, collectionName) => {
    const ids = (typeof ids === 'number') ? [target] : target;
    return repository(recordName).loadBatch(ids).then(response => ({
      recordName,
      collectionName,
      records: response.getData().data
    }))
  }
);

export const setCollection = createAction(
  'RECORDS_STORE_SET_COLLECTION',
  (recordName, collectionName, records) => ({recordName, collectionName, records})
);

export const releaseCollection = createAction(
  'RECORDS_STORE_RELEASE_COLLECTION',
  (recordName, collectionName) => ({recordName, collectionName})
);

