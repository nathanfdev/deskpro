import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import { mapKeyedFromArray } from 'DeskPRO/Component/Util/Map';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import Immutable from 'immutable';

window.Immutable = Immutable;

export const gcPeople         = createAction('GC_PEOPLE',              recordStoreActions.gcRecords());
export const releasePeople    = createAction('RELEASE_PEOPLE',         recordStoreActions.releaseRecords());
export const releaseRequest   = createAction('RELEASE_PEOPLE_REQUEST', recordStoreActions.releaseRequest());
export const setPeopleRequest = createAction('SET_PEOPLE',             recordStoreActions.setRequestRecords());
export const loadPeople       = createAction(
  'LOAD_PEOPLE',
  recordStoreActions.requestRecords(
    ['RecordStores', 'people'],
    missingIds => new Promise(
      (resolve, reject) => {
        console.error('loading people');
        DpApi.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','))
          .success(data => resolve(Immutable.fromJS(mapKeyedFromArray(data.data, 'id'))))
          .error(res => reject(res));
      }
    )
  )
);
export const loadAgents       = createAction(
  loadPeople.type,
  recordStoreActions.createRecordsRequest(
    ['RecordStores', 'people'],
    'agents',
    () => new Promise((resolve, reject) =>
      DpApi.sendGet('DP_API/agents')
           .success(response => resolve(response.data))
           .error(response => reject(response)))
  )
);