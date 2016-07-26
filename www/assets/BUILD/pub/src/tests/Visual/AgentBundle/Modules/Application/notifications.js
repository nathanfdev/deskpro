import React from 'react';
import { storiesOf, action } from '@kadira/storybook';
import { css } from 'Visual/decorators';
import { Notifications } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/Notifications/notifications';
import {
  infoNotificationFakeState,
  errorNotificationFakeState,
  delayedActionNotificationFakeState,
  undoableActionNotificationFakeState
} from 'DemoState/AgentBundle/Modules/Application/notifications';

const destroyNotification = () => () => action('destroy');

storiesOf('App: notifications', module)
  .addDecorator(story => css(story()))
  .add(
    'Info', () =>
    <Notifications
      notifications={[infoNotificationFakeState]}
      destroyNotification={destroyNotification}
    />
  )
  .add(
    'Error', () =>
    <Notifications
      notifications={[errorNotificationFakeState]}
      destroyNotification={destroyNotification}
    />
  )
  .add(
    'Delayed', () =>
    <Notifications
      notifications={[delayedActionNotificationFakeState]}
      destroyNotification={destroyNotification}
    />
  )
  .add(
    'Undoable', () =>
    <Notifications
      notifications={[undoableActionNotificationFakeState]}
      destroyNotification={destroyNotification}
    />
  )
  .add(
    'All together', () =>
    <Notifications
      notifications={[
        infoNotificationFakeState,
        errorNotificationFakeState,
        delayedActionNotificationFakeState,
        undoableActionNotificationFakeState
      ]}
      destroyNotification={destroyNotification}
    />
  )
;
