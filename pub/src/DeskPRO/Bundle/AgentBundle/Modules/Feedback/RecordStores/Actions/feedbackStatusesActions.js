import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackStatuses = createAction(
  'LOAD_FEEDBACK_STATUSES',
  requestRecords(
    ['RecordStores', 'feedback', 'statuses'],
      missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_statuses?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
