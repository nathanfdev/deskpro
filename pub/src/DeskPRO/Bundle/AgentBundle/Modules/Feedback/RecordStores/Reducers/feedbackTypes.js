import * as actions from '../Actions/feedbackTypesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: actions.loadFeedbackTypes,
    setRequestRecordAction: actions.setFeedbackTypesRequest
  })
);
