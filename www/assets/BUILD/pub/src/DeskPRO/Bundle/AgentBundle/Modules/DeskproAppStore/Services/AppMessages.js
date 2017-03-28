export const EVENT_MOUNT = 'mount';

export const EVENT_CONTEXTINIT = 'context-init';

export const EVENT_FIND_ALL_STATE = 'get-all-state';

export const EVENT_GET_STATE = 'get-state';

export const EVENT_SAVE_STATE = 'save-state';

export const eventNames = [
  EVENT_MOUNT,

  EVENT_CONTEXTINIT,

  EVENT_FIND_ALL_STATE,

  EVENT_GET_STATE,

  EVENT_SAVE_STATE
];

export const isEventName = name => eventNames.indexOf(name) !== -1;


