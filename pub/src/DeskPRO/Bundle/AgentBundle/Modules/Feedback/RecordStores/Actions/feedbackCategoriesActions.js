import { createAction } from 'Ampliflux';
import { createRecordsRequest } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackCategories = createAction(
  'LOAD_FEEDBACK_CATEGORIES',
  createRecordsRequest(
    ['RecordStores', 'Feedback', 'feedback', 'categories'], 'all',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_types')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
