import * as languagesActions from '../Actions/languagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: languagesActions.releaseLanguages,
    releaseRequestAction: languagesActions.releaseLanguagesRequest,
    setRequestRecordAction: languagesActions.setLanguagesRequest,
    requestRecordsAction: languagesActions.loadAll
  })
);
