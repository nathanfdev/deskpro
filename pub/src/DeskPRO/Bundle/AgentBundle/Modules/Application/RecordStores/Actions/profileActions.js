import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseProfile = createAction('RELEASE_PROFILE', recordStoreActions.releaseRecords());
export const releaseProfileRequest = createAction('RELEASE_PROFILE_REQUEST', recordStoreActions.releaseRequest());
export const setProfileRequest = createAction('SET_PROFILE', recordStoreActions.setRequestRecords());

export const loadMyProfile = createAction(
  'LOAD_PROFILE',
  createRecordsRequest(
    ['RecordStores', 'CRM', 'profile'],
    'my',
    () => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/me/profile')
          .success(response => resolve([response.data]))
          .error((data, response) => reject(response))
    )
  )
);
