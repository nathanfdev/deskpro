import * as actions from '../Actions/linkedItemActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: actions.releaseLinkedItems,
    releaseRequestAction: actions.releaseLinkedItemRequest,
    setRequestRecordAction: actions.setLinkedItemRequest,
    requestRecordsAction: actions.loadLinkedItems
  })
);
