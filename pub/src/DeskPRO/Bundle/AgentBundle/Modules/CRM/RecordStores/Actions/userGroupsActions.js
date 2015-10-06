import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const gcUserGroups         = createAction('GC_USER_GROUPS',              recordStoreActions.gcRecords());
export const releaseUserGroups    = createAction('RELEASE_USER_GROUPS',         recordStoreActions.releaseRecords());
export const releaseRequest       = createAction('RELEASE_USER_GROUPS_REQUEST', recordStoreActions.releaseRequest());
export const setUserGroupsRequest = createAction('SET_USER_GROUPS',             recordStoreActions.setRequestRecords());
export const loadAllUserGroups    = createAction(
  'LOAD_USER_GROUPS',
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'CRM', 'userGroups'],
    'all',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/user_groups')
        .success(response => resolve(response.data))
        .error(response => reject(response)))
  )
);