import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releaseUserGroups = createAction('RELEASE_USER_GROUPS', recordStoreActions.releaseRecords());
export const releaseRequest = createAction('RELEASE_USER_GROUPS_REQUEST', recordStoreActions.releaseRequest());
export const setUserGroupsRequest = createAction('SET_USER_GROUPS_REQUEST', recordStoreActions.setRequestRecords());
