import * as actions from '../Actions/notificationActions.js';
import { createReducer } from 'Ampliflux';
import { mergeValue } from 'Ampliflux/reducers/handlers';
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
  )
});
