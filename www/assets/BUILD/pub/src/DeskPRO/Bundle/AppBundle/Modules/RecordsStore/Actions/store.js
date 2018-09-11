import { createAction } from 'Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import Immutable from 'immutable';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';


// loadAll(), loadBatch() and loadFromApi() have the same ID because they share reducer --------------------------------

export const loadBatch = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, ids, collectionName) => (dispatch, getState) => {
    const numericIds = [];
    const pushId = (id) => {
      const intId = parseInt(id, 10);
      if (intId) {
        numericIds.push(intId);
      }
    };

    if (ids instanceof Array) {
      ids.forEach(id => pushId(id));
    } else {
      pushId(ids);
    }

    const recordStore = getState().RecordsStore.store.get(recordName);
    const loadedRecords = recordStore && recordStore.has('records') ? recordStore.get('records') : Immutable.fromJS({});
    const loadedIds = recordStore && recordStore.hasIn(['collections', collectionName]) ? recordStore.getIn(['collections', collectionName]) : Immutable.fromJS({});

    const targets = [];

    numericIds.forEach((id) => {
      if (!loadedRecords.has(id) && !loadedRecords.has(id.toString())
        && !loadedIds.has(id) && !loadedIds.has(id.toString())
      ) {
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
        ids:     allCollectionIds,
        promise: repository(recordName).loadBatch(targets).then((response) => {
          const targetRecords = mapKeyedFromArray(response.getData().data, 'id');
          const newData = loadedRecords.merge(targetRecords);

          return {
            recordName,
            collectionName,
            ids:     allCollectionIds,
            records: newData
          };
        })
      };
    } else {
      result = {
        recordName,
        collectionName,
        ids:     allCollectionIds,
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

export const loadWithParams = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, params, collectionName) => ({
    recordName,
    collectionName: 'all',
    ids:            [],
    promise:        repository(recordName).search(params).then((response) => {
      const records = response.getData().data;
      const ids = records.map(record => record.id);

      return {
        recordName,
        ids,
        records,
        collectionName
      };
    })
  })
);

export const loadFromApi = createAction(
  'RECORDS_STORE_LOAD',
  (recordName, url, collectionName) => (dispatch, getState) => {
    const recordStore = getState().RecordsStore.store.get(recordName);

    let result;
    if (!recordStore || !recordStore.hasIn(['statuses', collectionName])) {
      result = {
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
      };
    } else {
      result = {
        noUpdates: true
      };
    }

    return result;
  }
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
