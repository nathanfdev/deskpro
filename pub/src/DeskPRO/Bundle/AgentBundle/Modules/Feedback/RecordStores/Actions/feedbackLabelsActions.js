import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackLabels = createAction(
  'LOAD_FEEDBACK_LABELS',
  createRecordsRequest(
    ['RecordStores', 'feedbackLabels'], 'all',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_labels_list')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
