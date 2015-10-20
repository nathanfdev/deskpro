import * as profileActions from '../Actions/profileActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: profileActions.releaseProfile,
    releaseRequestAction: profileActions.releaseProfileRequest,
    setRequestRecordAction: profileActions.setProfileRequest
  })
);
