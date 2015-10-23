import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseProfile = createAction('RELEASE_PROFILES', recordStoreActions.releaseRecords());
export const releaseProfileRequest = createAction('RELEASE_PROFILES_REQUEST', recordStoreActions.releaseRequest());
export const setProfileRequest = createAction('SET_PROFILES', recordStoreActions.setRequestRecords());

export const loadMy = createAction(
  'LOAD_PROFILES',
  createRecordsRequest(
    ['RecordStores', 'CRM', 'profiles'],
    'my',
    () => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/me/profile')
          .success(response => resolve([response.data]))
          .error((data, response) => reject(response))
    )
  )
);
