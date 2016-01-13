import * as actions from '../Actions/peopleActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releasePeople,
    releaseRequestAction: actions.releasePeopleRequest,
    setRequestRecordAction: actions.setPeopleRequest,
    requestRecordsAction: actions.loadOnlineAgents
  })
);
