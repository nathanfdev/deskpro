import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import * as departmentsActions from '../Actions/departmentsActions';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    releaseRecordsAction: departmentsActions.releaseDepartments,
    releaseRequestAction: departmentsActions.releaseDepartmentsRequest,
    setRequestRecordAction: departmentsActions.setDepartmentsRequest,
    requestRecordsAction: departmentsActions.loadDepartments
  })
);
