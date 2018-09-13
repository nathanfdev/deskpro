import { createAction } from 'Ampliflux';

export const infoNotification = createAction('APP_NOTIFICATION_INFO');
export const errorNotification = createAction('APP_NOTIFICATION_ERROR');
export const delayedActionNotification = createAction('APP_NOTIFICATION_DELAYED_ACTION');
export const undoableActionNotification = createAction('APP_NOTIFICATION_UNDOABLE_ACTION');
export const destroyNotification = createAction('APP_NOTIFICATION_DESTROY');

export const setupActionAlerts = createAction('SETUP_ACTION_ALERTS');
export const newActionAlerts = createAction('NEW_ACTION_ALERTS');
