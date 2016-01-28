import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseTimezones = createAction('RELEASE_TIMEZONES', recordStoreActions.releaseRecords());
export const releaseTimezonesRequest = createAction('RELEASE_TIMEZONES_REQUEST', recordStoreActions.releaseRequest());
export const setTimezonesRequest = createAction('SET_TIMEZONES', recordStoreActions.setRequestRecords());

export const loadAll = createAction(
  'LOAD_TIMEZONES',
  createRecordsRequest(
    ['RecordStores', 'Common', 'timezones'],
    'all',
    () => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/timezones')
          .success(response => resolve(response.data))
          .error((data, response) => reject(response))
    )
  )
);
