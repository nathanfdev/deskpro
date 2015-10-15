import * as actions from '../Actions/taskLabelActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseTaskLabels,
    releaseRequestAction: actions.releaseTaskLabelRequest,
    setRequestRecordAction: actions.setTaskLabelRequest,
    requestRecordsAction: actions.loadAllTaskLabels
  })
);
