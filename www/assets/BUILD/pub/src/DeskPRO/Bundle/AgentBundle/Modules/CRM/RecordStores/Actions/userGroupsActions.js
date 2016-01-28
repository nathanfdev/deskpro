import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';

export const releaseUserGroups = createAction('RELEASE_USER_GROUPS', rsa.releaseRecords());
export const releaseRequest = createAction('RELEASE_USER_GROUPS_REQUEST', rsa.releaseRequest());
export const setUserGroupsRequest = createAction('SET_USER_GROUPS_REQUEST', rsa.setRequestRecords());
