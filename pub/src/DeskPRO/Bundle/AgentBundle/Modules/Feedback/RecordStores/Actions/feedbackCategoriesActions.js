import { createAction } from 'Ampliflux';
import { requestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const loadFeedbackCategories = createAction(
  'LOAD_FEEDBACK_CATEGORIES',
  requestRecords(
    ['RecordStores', 'Feedback', 'feedback', 'categories'],
      missingIds => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_categories?ids=' + missingIds.toArray().join(','))
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
