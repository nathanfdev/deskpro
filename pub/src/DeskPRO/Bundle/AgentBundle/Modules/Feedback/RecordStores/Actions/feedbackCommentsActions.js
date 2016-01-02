import { createAction } from 'Ampliflux';
import { requestRecords, setRequestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const setFeedbackCommentsRequest = createAction('SET_FEEDBACK_COMMENTS_REQUEST', setRequestRecords());

export const loadFeedbackCommentsCounter = createAction(
  'LOAD_FEEDBACK_COMMENTS_COUNTER',
  requestRecords(
    ['RecordStores', 'Feedback', 'feedback', 'comments'],
    missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_comments_counter?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
