import * as actions from '../Actions/notificationActions.js';
import { createReducer } from 'Ampliflux';
import uuid from 'node-uuid';

const initialState = {
  notifications: []
};

function addNotification(type) {
  return (state, payload) =>
    state.set('notifications', state.get('notifications').push({...payload, id: uuid(), type}));
}

export default createReducer(initialState, {
  [actions.infoNotification]: addNotification('info'),
  [actions.errorNotification]: addNotification('error'),
  [actions.delayedActionNotification]: addNotification('delayed'),
  [actions.undoableActionNotification]: addNotification('undoable'),
  [actions.destroyNotification]: (state, payload) => state.set(
    'notifications',
    state.get('notifications').filter(notification => notification.id !== payload)
  ),
  [actions.setupActionAlerts]: (state, payload) => state.set(
    'actionAlerts',
    payload.uuid),
  [actions.pollActionAlerts]: (state, payload) => {
    const last = payload[payload.length - 1];
    console.log(payload, last);
    if (last && last.uuid) {
      return state.set(
        'actionAlerts',
        last.uuid);
    }

    return state;
  }
});
