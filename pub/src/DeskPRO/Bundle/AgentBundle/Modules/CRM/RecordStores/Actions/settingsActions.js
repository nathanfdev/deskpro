import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const releaseSettings = createAction('RELEASE_SETTINGS', rsa.releaseRecords());
export const releaseSettingsRequest = createAction('RELEASE_SETTINGS_REQUEST', rsa.releaseRequest());
export const setSettingsRequest = createAction('SET_SETTINGS', rsa.setRequestRecords());

export const loadMy = createAction(
  'LOAD_SETTINGS',
  createRecordsRequest(
    ['RecordStores', 'CRM', 'settings'],
    'my',
    () => new Promise((resolve, reject) =>
      api.sendGet('DP_API/person_setting')
        .success(response => resolve(response.data))
        .error((data, response) => reject(response))
    )
  )
);
