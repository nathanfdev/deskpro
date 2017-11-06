import { createReducer } from 'Ampliflux';
import uuid from 'uuid';
import * as actions from '../Actions/notificationActions';

const initialState = {
  notifications:     [],
  actionAlerts:      {},
  actionAlertsSetup: true
};

function addNotification(type) {
  return (state, payload) =>
    state.set('notifications', state.get('notifications').push({ ...payload, id: uuid(), type }));
}

export default createReducer(initialState, {
  [actions.infoNotification]:           addNotification('info'),
  [actions.errorNotification]:          addNotification('error'),
  [actions.delayedActionNotification]:  addNotification('delayed'),
  [actions.undoableActionNotification]: addNotification('undoable'),
  [actions.destroyNotification]:        (state, payload) => state.set(
    'notifications',
    state.get('notifications').filter(notification => notification.id !== payload)
  ),
  [actions.setupActionAlerts]: (state, payload) => {
    const newState = state.set('actionAlerts', payload);
    return newState.set('actionAlertsSetup', false);
  },
  [actions.newActionAlerts]: (state, payload) => {
    const last = payload[payload.length - 1];
    if (last && last.uuid) {
      return state.set(
        'actionAlerts',
        last.uuid);
    }

    return state;
  }
});
