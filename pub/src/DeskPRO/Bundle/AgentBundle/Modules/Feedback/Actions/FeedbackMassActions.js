import { createAction } from 'Ampliflux';
import * as FeedbackAPI from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { applyParams } from './FeedbackListActions.js';

export const toggleMassAction = createAction('FEEDBACK_TOGGLE_MASS_ACTION');
export const toggleSelectedAction = createAction('FEEDBACK_TOGGLE_SELECTED_ACTION');
export const resetAllMassActionsParams = createAction('FEEDBACK_RESET_ALL_MASS_ACTIONS_PARAMS');

export const setMassActionsParams = createAction(
  'FEEDBACK_SET_MASS_ACTIONS_PARAMS',
    param => param
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
