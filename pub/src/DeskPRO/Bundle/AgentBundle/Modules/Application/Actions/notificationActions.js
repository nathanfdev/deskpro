import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import Immutable from 'immutable';

export const infoNotification = createAction('APP_NOTIFICATION_INFO');
export const errorNotification = createAction('APP_NOTIFICATION_ERROR');
export const delayedActionNotification = createAction('APP_NOTIFICATION_DELAYED_ACTION');
export const undoableActionNotification = createAction('APP_NOTIFICATION_UNDOABLE_ACTION');

export const destroyNotification = createAction('APP_NOTIFICATION_DESTROY');


export const setupActionAlerts = createAction(
  'SETUP_ACTION_ALERTS',
    () => {
      return new Promise(
        (resolve, reject) => {
          return DpApi.sendGet('DP_API/notify/setup/action-alerts')
            .success(response => {
              return resolve(response.data);
            })
            .error(response => reject(response));
        }
      );
    }
);


export const newMessages = createAction(
  'NEW_MESSAGES_ACTION',
  (messages) => messages
);

export const pollActionAlerts = createAction(
  'POLL_ACTION_ALERTS',
  () => (dispatch, getState) => {
    return new Promise(
      (resolve, reject) => {
        return DpApi.sendGet('DP_API/notify/action-alerts/' + getState().Application.notifications.get('actionAlerts'))
          .success(response => {
            let list = Immutable.List(response.data);
            list = list.filter((element) => element.type === 'notification.agent_chat.new_message');
            dispatch(newMessages(list));
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);

