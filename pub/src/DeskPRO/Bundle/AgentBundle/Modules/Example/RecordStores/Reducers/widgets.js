import * as widgetActions from '../Actions/widgetActions.js';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: widgetActions.release,
    releaseRequestAction: widgetActions.releaseRequest,
    setRequestRecordAction: widgetActions.setWidgets,
    requestRecordsAction: function() {}
  })
);
