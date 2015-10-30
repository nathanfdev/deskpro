import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseSettings = createAction('RELEASE_SETTINGS', recordStoreActions.releaseRecords());
export const releaseSettingsRequest = createAction('RELEASE_SETTINGS_REQUEST', recordStoreActions.releaseRequest());
export const setSettingsRequest = createAction('SET_SETTINGS', recordStoreActions.setRequestRecords());

export const loadMy = createAction(
  'LOAD_SETTINGS',
  createRecordsRequest(
    ['RecordStores', 'CRM', 'settings'],
    'my',
    () => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/person_setting')
          .success(response => resolve(response.data))
          .error((data, response) => reject(response))
    )
  )
);
