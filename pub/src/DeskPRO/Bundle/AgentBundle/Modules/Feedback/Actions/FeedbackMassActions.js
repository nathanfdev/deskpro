import { createAction } from 'Ampliflux';
import * as FeedbackAPI from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { applyParams } from './FeedbackListActions';

export const massAction = createAction(
  'FEEDBACK_MASS_ACTION',
  (params) => (dispatch) =>
    FeedbackAPI.massAction(params)
      .then(promise => {
        dispatch(applyParams());
        return promise.getData();
      }
    )
);
