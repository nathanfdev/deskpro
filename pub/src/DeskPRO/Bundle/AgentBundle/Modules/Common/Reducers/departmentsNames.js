import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import * as DepartmentsNamesActions from '../Actions/DepartmentsNamesActions';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction:               DepartmentsNamesActions.gcDepartmentsNames,
    releaseRecordsAction:   DepartmentsNamesActions.releaseDepartmentsNames,
    releaseRequestAction:   DepartmentsNamesActions.releaseDepartmentsNamesRequest,
    setRequestRecordAction: DepartmentsNamesActions.setDepartmentsNamesRequest,
    requestRecordsAction:   DepartmentsNamesActions.loadDepartmentsNames
  })
);
