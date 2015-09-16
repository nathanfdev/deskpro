import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { loadNames } from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';

const statePath   = ['Common', 'peopleNames'];
const loadMissing = (ids) => loadNames(ids).then((response) => mapKeyedFromArray(response.getData().data, 'id'));

export const gcPeopleNames             = createAction('GC_PEOPLE_NAMES',              rsa.gcRecords());
export const releasePeopleNames        = createAction('RELEASE_PEOPLE_NAMES',         rsa.releaseRecords());
export const releasePeopleNamesRequest = createAction('RELEASE_PEOPLE_NAMES_REQUEST', rsa.releaseRequest());
export const setPeopleNamesRequest     = createAction('SET_PEOPLE_NAMES',             rsa.setRequestRecords());
export const loadPeopleNames           = createAction('LOAD_PEOPLE_NAMES', rsa.requestRecords(statePath, loadMissing));
