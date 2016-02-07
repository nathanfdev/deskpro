import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

export const releaseProfiles = createAction('RELEASE_PROFILES', rsa.releaseRecords());
export const releaseProfilesRequest = createAction('RELEASE_PROFILES_REQUEST', rsa.releaseRequest());
export const setProfilesRequest = createAction('SET_PROFILES', rsa.setRequestRecords());

export const loadMy = createAction(
  'LOAD_PROFILES',
  createRecordsRequest(
    ['RecordStores', 'CRM', 'profiles'],
    'my',
    () => new Promise((resolve, reject) =>
      api.sendGet('DP_API/me/profile')
        .success(response => resolve([response.data]))
        .error((data, response) => reject(response))
    )
  )
);
