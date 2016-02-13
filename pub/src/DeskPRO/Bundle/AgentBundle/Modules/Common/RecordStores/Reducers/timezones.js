import * as timezonesActions from '../Actions/timezonesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: timezonesActions.releaseTimezones,
    releaseRequestAction: timezonesActions.releaseTimezonesRequest,
    setRequestRecordAction: timezonesActions.setTimezonesRequest,
    requestRecordsAction: timezonesActions.loadAll
  })
);
