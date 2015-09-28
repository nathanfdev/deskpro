import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackTypes = createAction(
  'LOAD_FEEDBACK_TYPES',
  createRecordsRequest(
    ['RecordStores', 'feedbackTypes'], 'all',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_types')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
