import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { loadNames } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Departments';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

const statePath   = ['Common', 'departmentNames'];
const loadMissing = (ids) => loadNames(ids).then((response) => mapKeyedFromArray(response.getData().data, 'id'));

export const gcDepartmentsNames             = createAction('GC_DEPARTMENTS_NAMES',              rsa.gcRecords());
export const releaseDepartmentsNames        = createAction('RELEASE_DEPARTMENTS_NAMES',         rsa.releaseRecords());
export const releaseDepartmentsNamesRequest = createAction('RELEASE_DEPARTMENTS_NAMES_REQUEST', rsa.releaseRequest());
export const setDepartmentsNamesRequest = createAction('SET_DEPARTMENTS_NAMES', rsa.setRequestRecords());
export const loadDepartmentsNames = createAction('LOAD_DEPARTMENTS_NAMES', rsa.requestRecords(statePath, loadMissing));
