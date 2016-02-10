import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const releaseTimezones = createAction('RELEASE_TIMEZONES', rsa.releaseRecords());
export const releaseTimezonesRequest = createAction('RELEASE_TIMEZONES_REQUEST', rsa.releaseRequest());
export const setTimezonesRequest = createAction('SET_TIMEZONES', rsa.setRequestRecords());

export const loadAll = createAction(
  'LOAD_TIMEZONES',
  createRecordsRequest(
    ['RecordStores', 'Common', 'timezones'],
    'all',
    () => new Promise((resolve, reject) =>
      api.sendGet('DP_API/timezones')
        .success(response => resolve(response.data))
        .error((data, response) => reject(response))
    )
  )
);
