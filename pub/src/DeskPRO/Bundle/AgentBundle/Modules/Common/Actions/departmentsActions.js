import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { load } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

const statePath   = ['Common', 'department'];
const loadMissing = (ids) => load(ids).then(response => response.getData().data);

export const gcDepartments             = createAction('GC_DEPARTMENTS',              rsa.gcRecords());
export const releaseDepartments        = createAction('RELEASE_DEPARTMENTS',         rsa.releaseRecords());
export const releaseDepartmentsRequest = createAction('RELEASE_DEPARTMENTS_REQUEST', rsa.releaseRequest());
export const setDepartmentsRequest     = createAction('SET_DEPARTMENTS',             rsa.setRequestRecords());
export const loadDepartments           = createAction('LOAD_DEPARTMENTS', rsa.requestRecords(statePath, loadMissing));
