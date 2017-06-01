// STATE EVENTS

export const EVENT_WEBAPI_REQUEST_DESKPRO = 'webapi.request.deskpro';

export const EVENT_STATE_FIND = 'state.find';

export const EVENT_STATE_GET = 'state.get';

export const EVENT_STATE_SET = 'state.set';

export const EVENT_STATE_DELETE = 'state.delete';

// TAB EVENTS

export const EVENT_TAB_DATA = 'context.tab_data';

export const EVENT_TAB_STATUS = 'context.tab_status';

export const EVENT_TAB_ACTIVATE = 'context.tab_activate';

export const EVENT_TAB_CLOSE = 'context.tab_close';

// APP EVENTS

export const EVENT_RESET_SIZE = 'app.reset_size';

// USER EVENTS

export const EVENT_ME_GET = 'context.me_get';

export const events =
{
  // API REQUEST EVENTS

  EVENT_WEBAPI_REQUEST_DESKPRO,

  // STATE EVENTS

  EVENT_STATE_FIND,

  EVENT_STATE_GET,

  EVENT_STATE_SET,

  EVENT_STATE_DELETE,

  // TAB EVENTS

  EVENT_TAB_DATA,

  EVENT_TAB_STATUS,

  EVENT_TAB_ACTIVATE,

  EVENT_TAB_CLOSE,

  // APP EVENTS

  EVENT_RESET_SIZE,

  EVENT_ME_GET
};

export const eventNames = Object.keys(events).map(key => events[key]);

export const isEventName = name => eventNames.indexOf(name) !== -1;


