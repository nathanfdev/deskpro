export const EVENT_MOUNT = 'mount';

export const EVENT_STATE_FIND = 'state.find';

export const EVENT_STATE_GET = 'state.get';

export const EVENT_STATE_SET = 'state.set';

export const EVENT_STATE_DELETE = 'state.delete';

export const EVENT_USER_GET = 'get-user';

export const eventNames = [
  EVENT_MOUNT,

  EVENT_STATE_FIND,

  EVENT_STATE_GET,

  EVENT_STATE_SET,

  EVENT_STATE_DELETE,

  EVENT_USER_GET
];

export const isEventName = name => eventNames.indexOf(name) !== -1;


