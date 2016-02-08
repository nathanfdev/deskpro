import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const releasePeople = createAction('RELEASE_PEOPLE', rsa.releaseRecords());
export const releasePeopleRequest = createAction('RELEASE_PEOPLE_REQUEST', rsa.releaseRequest());
export const setPeopleRequest = createAction('SET_PEOPLE_REQUEST', rsa.setRequestRecords());
export const loadPeople = createAction(
  'LOAD_PEOPLE',
  rsa.requestRecords(
    ['RecordStores', 'CRM', 'people'],
    missingIds => new Promise(
      (resolve, reject) =>
        api.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','))
             .success(response => resolve(response.data))
             .error(response => reject(response))
    )
  )
);

export const updateOnline = createAction(
  'UPDATE_AGENTS_ONLINE',
  ids => ids
);
