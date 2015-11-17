import * as actions from '../Actions/taskListActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseTaskLists,
    releaseRequestAction: actions.releaseTaskListsRequest,
    setRequestRecordAction: actions.setTaskListsRequest,
    requestRecordsAction: actions.loadAllTaskLists
  })
);
