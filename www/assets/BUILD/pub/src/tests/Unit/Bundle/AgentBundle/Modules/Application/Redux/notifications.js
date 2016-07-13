// #define ~app DeskPRO/Bundle/AgentBundle/Modules/Application

import { createAgentApp } from 'Helpers';
import {
  infoNotificationDemoState,
  errorNotificationDemoState,
  delayedActionNotificationDemoState,
  undoableActionNotificationDemoState
}
  from 'DemoState/AgentBundle/Modules/Application/notifications';
import {
  infoNotification,
  errorNotification,
  delayedActionNotification,
  undoableActionNotification,
  destroyNotification
}
  from '~app/Actions/notificationActions';
import { notificationsSelector } from '~app/Selectors/notifications';

describe('App: Notification actions', () => {

  describe('infoNotification', () => {
    it('should result into a notification action with id and type equal to "info"', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(infoNotification(infoNotificationDemoState));

      const notifications = notificationsSelector(getState());
      const notification =  notifications.first();
      expect(notifications.count()).toEqual(1);
      expect(notification.id).toBeDefined();
      expect(notification.type).toEqual('info');
    });
  });

  describe('errorNotification', () => {
    it('should result into a notification action with id and type equal to "error"', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(errorNotification(errorNotificationDemoState));

      const notifications = notificationsSelector(getState());
      const notification =  notifications.first();
      expect(notifications.count()).toEqual(1);
      expect(notification.id).toBeDefined();
      expect(notification.type).toEqual('error');
    });
  });

  describe('delayedActionNotification', () => {
    it('should result into a notification action with id and type equal to "delayed"', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(delayedActionNotification(delayedActionNotificationDemoState));

      const notifications = notificationsSelector(getState());
      const notification =  notifications.first();
      expect(notifications.count()).toEqual(1);
      expect(notification.id).toBeDefined();
      expect(notification.type).toEqual('delayed');
    });
  });

  describe('undoableActionNotification', () => {
    it('should result into a notification action with id and type equal to "undoable"', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(undoableActionNotification(undoableActionNotificationDemoState));

      const notifications = notificationsSelector(getState());
      const notification =  notifications.first();
      expect(notifications.count()).toEqual(1);
      expect(notification.id).toBeDefined();
      expect(notification.type).toEqual('undoable');
    });
  });

  describe('Actions queue', () => {
    it('should contain 3 records after dispatching 3 action records', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(infoNotification(infoNotificationDemoState));
      dispatch(infoNotification(infoNotificationDemoState));
      dispatch(infoNotification(infoNotificationDemoState));

      expect(notificationsSelector(getState()).count()).toEqual(3);
    });

    it('should contain 1 record after dispatching 3 action records and destroying 2 of them', () => {
      const { dispatch, getState } = createAgentApp();
      dispatch(infoNotification(infoNotificationDemoState));
      dispatch(infoNotification(infoNotificationDemoState));
      dispatch(infoNotification(infoNotificationDemoState));
      const notifications = notificationsSelector(getState());
      expect(notificationsSelector(getState()).count()).toEqual(3);

      dispatch(destroyNotification(notifications.get(1).id));
      dispatch(destroyNotification(notifications.get(2).id));

      expect(notificationsSelector(getState()).count()).toEqual(1);
    });
  })

});
