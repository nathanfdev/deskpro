import * as widgetTypeActions from '../Actions/widgetTypeActions.js';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: widgetTypeActions.release,
    releaseRequestAction: widgetTypeActions.releaseRequest,
    setRequestRecordAction: widgetTypeActions.setTypes,
    requestRecordsAction: widgetTypeActions.loadTypes
  })
);
