import { createAction } from 'Ampliflux';
import * as rsa from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadPeople = createAction(
  'LOAD_PEOPLE',
  rsa.requestRecords(
    ['RecordStores', 'Application', 'people'],
      missingIds => new Promise((resolve, reject) =>
        DpApi.sendGet('DP_API/people?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
