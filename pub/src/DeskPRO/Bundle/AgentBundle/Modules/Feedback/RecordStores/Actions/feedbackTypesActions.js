import { createAction } from 'Ampliflux';
import { createRecordsRequest, setRequestRecords } from 'Ampliflux/common/record-store/actions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const setFeedbackTypesRequest = createAction('SET_FEEDBACK_TYPES_REQUEST', setRequestRecords());

export const loadFeedbackTypes = createAction(
  'LOAD_FEEDBACK_TYPES',
  createRecordsRequest(
    ['RecordStores', 'Feedback', 'feedbackTypes'], 'all',
    () => new Promise(
      (resolve, reject) =>
        DpApi.sendGet('DP_API/feedback_types')
          .success(response => resolve(response.data))
          .error(response => reject(response))
    )
  )
);
