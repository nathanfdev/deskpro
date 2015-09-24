import * as agentTeamsActions from '../Actions/agentTeamsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: agentTeamsActions.gcagentTeams,
    releaseRecordsAction: agentTeamsActions.releaseagentTeams,
    releaseRequestAction: agentTeamsActions.releaseRequest,
    setRequestRecordAction: agentTeamsActions.setagentTeamsRequest,
    requestRecordsAction: agentTeamsActions.loadagentTeams
  })
);
