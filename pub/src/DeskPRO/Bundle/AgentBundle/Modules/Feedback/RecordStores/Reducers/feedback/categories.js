import { loadFeedbackCategories, setFeedbackCategoriesRequest } from '../../Actions/feedbackCategoriesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: loadFeedbackCategories,
    setRequestRecordAction: setFeedbackCategoriesRequest
  })
);
