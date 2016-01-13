import { createAction } from 'Ampliflux';
import * as FeedbackAPI from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { applyParams } from './FeedbackListActions';

export const resetAllMassActionsParams = createAction('FEEDBACK_RESET_ALL_MASS_ACTIONS_PARAMS');

export const setMassActionsParams = createAction(
  'FEEDBACK_SET_MASS_ACTIONS_PARAMS',
  (params) => {
    delete params.delayReload;
    return params;
  }
);

export const resetMassActionsParam = createAction(
  'FEEDBACK_RESET_MASS_ACTIONS_PARAM',
    param => param
);

export const massAction = createAction(
  'FEEDBACK_MASS_ACTION',
  (params) => (dispatch) =>
    FeedbackAPI.massAction(params)
      .then(promise => {
        dispatch(resetAllMassActionsParams());
        dispatch(applyParams());
        return promise.getData();
      }
    )
);
