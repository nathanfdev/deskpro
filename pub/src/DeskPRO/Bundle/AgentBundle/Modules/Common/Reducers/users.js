import * as UserActions from '../Actions/UserActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: UserActions.gcUsers,
    releaseRecordsAction: UserActions.releaseUsers,
    releaseRequestAction: UserActions.releaseRequest,
    setRequestRecordAction: UserActions.setUserRequest,
    requestRecordsAction: UserActions.loadUsers
  })
);
