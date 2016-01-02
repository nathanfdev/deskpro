import { loadFeedback, setFeedbackRequest } from '../Actions/feedbackActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    setRequestRecordAction: setFeedbackRequest,
    requestRecordsAction: loadFeedback
  })
);
