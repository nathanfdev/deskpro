import { createAction } from 'Ampliflux';
import * as recordStoreActions from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadEmails = createAction(
  'LOAD_EMAILS',
  recordStoreActions.requestRecords(
    ['RecordStores', 'CRM', 'emails`'],
      // Attention! In this case ids is the list of person IDs, not email ID
      missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/emails?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
