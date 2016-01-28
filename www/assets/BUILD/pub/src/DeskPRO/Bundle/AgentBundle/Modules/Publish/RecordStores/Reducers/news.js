import { setNewsRequest } from '../Actions/newsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    setRequestRecordAction: setNewsRequest
  })
);
