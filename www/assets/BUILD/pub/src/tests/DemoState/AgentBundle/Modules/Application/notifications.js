import { action } from 'Helpers';

/*
 * There are 4 types of notifications: info, error, delayed action, undoable.
 *
 * Each of the types has its' own Redux action and all this actions put notifications into a single data structure
 * adding "type" and "id" props to the original action payload.
 *
 * This file contains DemoState constants representing notification action payloads and corresponding FakeState
 * constants representing a notification within Redux state after adding "id" and "type" props. We need DemoState
 * constants to test notification actions and we need FakeState to test notification React components outside of the
 * Redux context.
 */

export const infoNotificationDemoState = {
  title: 'John Smith has sent you a file'
};
export const infoNotificationFakeState = {
  id:    1,
  type:  'info',
  ...infoNotificationDemoState
};

export const errorNotificationDemoState = {
  title: 'An error occurred',
  text:  'Detailed information'
};
export const errorNotificationFakeState = {
  id:    2,
  type:  'error',
  ...errorNotificationDemoState
};

export const delayedActionNotificationDemoState = {
  alive:  25,
  title:  'Are you sure you want to send 42 emails to your colleagues?',
  text:   'This one has custom alive period of 25 sec',
  commit: action('commit'),
  undo:   action('undo')
};
export const delayedActionNotificationFakeState = {
  id:     3,
  type:   'delayed',
  ...delayedActionNotificationDemoState
};

export const undoableActionNotificationDemoState = {
  title:  '100+ tickets were deleted',
  text:   'Additional information',
  undo:   action('undo')
};
export const undoableActionNotificationFakeState = {
  id:     4,
  type:   'undoable',
  ...undoableActionNotificationDemoState
};
