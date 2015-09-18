import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import * as DepartmentsActions from '../Actions/departmentsActions';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction:               DepartmentsActions.gcDepartments,
    releaseRecordsAction:   DepartmentsActions.releaseDepartments,
    releaseRequestAction:   DepartmentsActions.releaseDepartmentsRequest,
    setRequestRecordAction: DepartmentsActions.setDepartmentsRequest,
    requestRecordsAction:   DepartmentsActions.loadDepartments
  })
);
