import * as profileActions from '../Actions/profilesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: profileActions.releaseProfiles,
    releaseRequestAction: profileActions.releaseProfilesRequest,
    setRequestRecordAction: profileActions.setProfilesRequest,
    requestRecordsAction: profileActions.loadMy
  })
);
