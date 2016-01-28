import * as organizationsActions from '../Actions/organizationsActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: organizationsActions.releaseOrganizations,
    releaseRequestAction: organizationsActions.releaseOrganizationsRequest,
    setRequestRecordAction: organizationsActions.setOrganizationsRequest,
    requestRecordsAction: organizationsActions.loadOrganizations
  })
);
