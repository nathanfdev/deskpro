import * as userGroupsActions from '../Actions/userGroupsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: userGroupsActions.releaseUserGroups,
    releaseRequestAction: userGroupsActions.releaseRequest,
    setRequestRecordAction: userGroupsActions.setUserGroupsRequest,
    requestRecordsAction: userGroupsActions.loadAllUserGroups
  })
);
