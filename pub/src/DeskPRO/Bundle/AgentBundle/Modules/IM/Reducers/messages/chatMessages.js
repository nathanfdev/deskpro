import * as actions from '../../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: actions.gcMessages,
    releaseRecordsAction: actions.releaseMessages,
    releaseRequestAction: actions.releaseMessageRequest,
    setRequestRecordAction: actions.setMessagesRequest,
    requestRecordsAction: actions.loadRecentMessages
  })
);
