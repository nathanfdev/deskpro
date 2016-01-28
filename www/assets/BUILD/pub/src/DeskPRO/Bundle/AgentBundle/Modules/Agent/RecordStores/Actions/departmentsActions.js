import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { load as apiLoad, loadDepartments as apiLoadDepartments } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';

const statePath = ['RecordStores', 'Agent', 'departments'];
const loadMissing = (ids) => apiLoad(ids).then(response => response.getData().data);

export const releaseDepartments = createAction('RELEASE_DEPARTMENTS', rsa.releaseRecords());
export const releaseDepartmentsRequest = createAction('RELEASE_DEPARTMENTS_REQUEST', rsa.releaseRequest());
export const setDepartmentsRequest = createAction('SET_DEPARTMENTS_REQUEST', rsa.setRequestRecords());
export const loadDepartments = createAction('LOAD_DEPARTMENTS', rsa.requestRecords(statePath, loadMissing));
