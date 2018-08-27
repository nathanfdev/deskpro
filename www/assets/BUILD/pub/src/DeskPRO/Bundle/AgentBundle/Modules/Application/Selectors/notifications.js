import { createSelector } from 'reselect';

const stateSelector = state => state.Application.notifications;

export const notificationsSelector = createSelector(
  stateSelector,
  state => state.get('notifications')
);

export const actionAlertsSelector = createSelector(
  stateSelector,
  state => state.get('actionAlerts')
);
