import { createReducer } from 'Ampliflux';
import { loadDepartmentAvatars, releaseDepartmentAvatarsRequest } from '../../Actions/avatarActions';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: loadDepartmentAvatars,
    releaseRequestAction: releaseDepartmentAvatarsRequest
  })
);
