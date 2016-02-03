import { createAction } from 'Ampliflux';
import { requestRecords, setRequestRecords } from 'Ampliflux/common/record-store/actions';
import { api } from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const setFeedbackRequest = createAction('SET_FEEDBACK_REQUEST', setRequestRecords());

export const loadFeedback = createAction(
  'LOAD_FEEDBACK',
  requestRecords(
    ['RecordStores', 'Feedback', 'feedback', 'feedback'],
      missingIds => new Promise(
      (resolve, reject) =>
        api.sendGet('DP_API/feedback?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
