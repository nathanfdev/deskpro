import { createReducer } from 'Ampliflux';
import { loadAgentTeamAvatars, releaseAgentTeamAvatarsRequest } from '../../Actions/avatarActions';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: loadAgentTeamAvatars,
    releaseRequestAction: releaseAgentTeamAvatarsRequest
  })
);
