import * as settingsActions from '../Actions/settingsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: settingsActions.releaseSettings,
    releaseRequestAction: settingsActions.releaseSettingsRequest,
    setRequestRecordAction: settingsActions.setSettingsRequest,
    requestRecordsAction: settingsActions.loadMy
  })
);
