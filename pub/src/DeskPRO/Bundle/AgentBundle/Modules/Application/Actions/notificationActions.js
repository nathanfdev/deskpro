import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';

export const infoNotification = createAction('APP_NOTIFICATION_INFO');
export const errorNotification = createAction('APP_NOTIFICATION_ERROR');
export const delayedActionNotification = createAction('APP_NOTIFICATION_DELAYED_ACTION');
export const undoableActionNotification = createAction('APP_NOTIFICATION_UNDOABLE_ACTION');

export const destroyNotification = createAction('APP_NOTIFICATION_DESTROY');


export const setupActionAlerts = createAction(
  'SETUP_ACTION_ALERTS',
    (data) => data
);

export const newActionAlerts = createAction(
  'NEW_ACTION_ALERTS',
  (data) => data
);

export const pollActionAlerts = createAction(
  'POLL_ACTION_ALERTS',
  () => (dispatch, getState) => {
    return new Promise(
      (resolve, reject) => {
        return DpApi.sendGet('DP_API/notify/action-alerts/' + getState().Application.notifications.get('actionAlerts'))
          .success(response => {
            if (response.data.length > 0) {
              dispatch(newActionAlerts(response.data));
            }
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);

