import { createAction } from 'Ampliflux';

export const toggleMassAction = createAction('APP_TOGGLE_MASS_ACTION');
export const toggleSelectedAction = createAction('APP_TOGGLE_SELECTED_ACTION');
export const cancelMassActions = createAction('APP_CANCEL_MASS_ACTIONS');
export const setMassActionsParams = createAction(
  'APP_SET_MASS_ACTIONS_PARAMS',
  (params) => {
    delete params.delayReload;
    return params;
  }
);

export const resetParam = createAction(
  'APP_RESET_MASS_ACTIONS_PARAM',
    param => param
);
