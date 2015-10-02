import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackCommentsCounter = createAction(
  'LOAD_FEEDBACK_COMMENTS_COUNTER',
  requestRecords(
    ['RecordStores', 'feedback', 'comments'],
    missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_comments_counter?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
