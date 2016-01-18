import * as actions from '../Actions/chatsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    requestRecordsAction: actions.loadChats,
    setRequestRecordAction: actions.setChatsRequest,
    releaseRecordsAction: actions.releaseChats,
    releaseRequestAction: actions.releaseRequest
  })
);


