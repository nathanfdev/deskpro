import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const releasePeople = createAction('RELEASE_PEOPLE', recordStoreActions.releaseRecords());
export const releasePeopleRequest = createAction('RELEASE_PEOPLE_REQUEST', recordStoreActions.releaseRequest());
export const setPeopleRequest = createAction('SET_PEOPLE_REQUEST', recordStoreActions.setRequestRecords());
export const loadPeople = createAction(
  'LOAD_PEOPLE',
  recordStoreActions.requestRecords(
    ['RecordStores', 'CRM', 'people'],
    missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','))
             .success(response => resolve(response.data))
             .error(response => reject(response))
    )
  )
);

export const updateOnline = createAction(
  'UPDATE_AGENTS_ONLINE',
  ids => ids
);