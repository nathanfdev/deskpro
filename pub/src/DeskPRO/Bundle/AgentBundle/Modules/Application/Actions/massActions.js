import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

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

export const getJobStatus = createAction(
  'APP_GET_JOB_STATUS',
  (id, reloadListAction, reloadNavAction) => (dispatch) =>
    api.sendGet('DP_API/mass_actions/' + id)
      .success(response => {
        if (response.data.status !== 'complete') {
          setTimeout(() => dispatch(getJobStatus(id, reloadListAction, reloadNavAction)), 2000);
        } else {
          dispatch(reloadNavAction());
          return dispatch(reloadListAction());
        }
      })
);

export const submitMassActions = createAction(
  'APP_MASS_ACTIONS_SUBMIT',
  (data) => (dispatch) => new Promise(resolve =>
    api.sendPost('DP_API/mass_actions/', { jobType: data.jobType, params: data.params })
      .success(response => {
        console.log('Job', response);
        dispatch(toggleMassAction());
        dispatch(cancelMassActions());
        dispatch(data.loadIndicatorAction());
        setTimeout(() => dispatch(getJobStatus(response.job, data.reloadListAction, data.reloadNavAction)), 350);
        return resolve(response);
      }))
);
