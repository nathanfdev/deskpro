import * as agentTeamsActions from '../Actions/agentTeamsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: agentTeamsActions.releaseAgentTeams,
    releaseRequestAction: agentTeamsActions.releaseRequest,
    setRequestRecordAction: agentTeamsActions.setAgentTeamsRequest,
    requestRecordsAction: agentTeamsActions.loadAllAgentTeams
  })
);
