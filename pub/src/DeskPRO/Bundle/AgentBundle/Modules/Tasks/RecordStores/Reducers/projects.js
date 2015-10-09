import * as actions from '../Actions/taskActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseProjects,
    releaseRequestAction: actions.releaseProjectRequest,
    setRequestRecordAction: actions.setProjectRequest,
    requestRecordsAction: actions.loadProjects
  })
);
