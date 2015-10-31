import * as actions from '../Actions/taskActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseTasks,
    releaseRequestAction: actions.releaseTaskRequest,
    setRequestRecordAction: actions.setTaskRequest,
    requestRecordsAction: actions.loadAllTasks
  })
);
