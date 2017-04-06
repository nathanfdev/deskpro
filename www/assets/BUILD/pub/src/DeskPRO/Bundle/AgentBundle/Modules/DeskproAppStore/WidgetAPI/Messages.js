export const EVENT_MOUNT = 'mount';

export const EVENT_CONTEXTINIT = 'context-init';

export const EVENT_STATE_FIND = 'find-state';

export const EVENT_STATE_GET = 'get-state';

export const EVENT_STATE_SAVE = 'save-state';

export const EVENT_STATE_DELETE = 'delete-state';

export const EVENT_STATE_CREATE = 'create-state';

export const EVENT_STATE_UPDATE = 'update-state';

export const eventNames = [
  EVENT_MOUNT,

  EVENT_CONTEXTINIT,

  EVENT_STATE_CREATE,

  EVENT_STATE_DELETE,

  EVENT_STATE_FIND,

  EVENT_STATE_GET,

  EVENT_STATE_SAVE,

  EVENT_STATE_UPDATE
];

export const isEventName = name => eventNames.indexOf(name) !== -1;


