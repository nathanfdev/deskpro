import * as actions from '../Actions/imChatsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: actions.gcChats,
    releaseRecordsAction: actions.releaseChats,
    releaseRequestAction: actions.releaseChatRequest,
    setRequestRecordAction: actions.setChatsRequest,
    requestRecordsAction: actions.loadChats
  })
);