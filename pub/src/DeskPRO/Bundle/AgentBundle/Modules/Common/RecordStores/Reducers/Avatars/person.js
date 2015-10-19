import { createReducer } from 'Ampliflux';
import { loadPersonAvatars, releasePersonAvatarsRequest } from '../../Actions/avatarActions';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: loadPersonAvatars,
    releaseRequestAction: releasePersonAvatarsRequest
  })
);
