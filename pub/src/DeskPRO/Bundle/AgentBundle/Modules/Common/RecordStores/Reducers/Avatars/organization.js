import { createReducer } from 'Ampliflux';
import { loadOrganizationAvatars, releaseOrganizationAvatarsRequest } from '../../Actions/avatarActions';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: loadOrganizationAvatars,
    releaseRequestAction: releaseOrganizationAvatarsRequest
  })
);
