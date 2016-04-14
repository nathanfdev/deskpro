import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const toggleMassAction     = createAction('APP_TOGGLE_MASS_ACTION');
export const toggleSelectedAction = createAction('APP_TOGGLE_SELECTED_ACTION');
export const cancelMassActions    = createAction('APP_CANCEL_MASS_ACTIONS');
export const setMassActionsParams = createAction('APP_SET_MASS_ACTIONS_PARAMS');

export const resetParam = createAction(
  'APP_RESET_MASS_ACTIONS_PARAM',
    param => param
);

export const submitMassActions = createAction(
  'APP_MASS_ACTIONS_SUBMIT',
  ({ content, ids, params, loadIndicatorAction, reloadNavAction }) => (dispatch) => {
    dispatch(loadIndicatorAction());
    return new Promise(resolve =>
      api.sendPost(`DP_API/mass_actions/${content}`, { ids, params })
        .success(response => {
          dispatch(toggleMassAction());
          dispatch(cancelMassActions());
          dispatch(reloadNavAction());
          return resolve(response);
        }));
  }
);
