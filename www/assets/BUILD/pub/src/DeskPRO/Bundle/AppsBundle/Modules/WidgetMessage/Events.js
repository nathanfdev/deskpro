// SECURITY EVENTS
export const EVENT_SECURITY_AUTHENTICATE_OAUTH = 'security.authenticate.oauth';

export const EVENT_SECURITY_SETTINGS_OAUTH = 'security.settings.oauth';

// FETCH EVENTS
export const EVENT_WEBAPI_REQUEST_FETCH = 'webapi.request.fetch';

export const EVENT_WEBAPI_REQUEST_DESKPRO = 'webapi.request.deskpro';

// TAB EVENTS

export const EVENT_TAB_DATA = 'context.tab_data';

export const EVENT_TAB_STATUS = 'context.tab_status';

export const EVENT_TAB_ACTIVATE = 'context.tab_activate';

export const EVENT_TAB_CLOSE = 'context.tab_close';

// TICKET EVENTS

export const EVENT_TICKET_REPLY = 'context.ticket.reply';

// APP EVENTS

export const EVENT_RESET_SIZE = 'app.reset_size';

export const EVENT_SUBSCRIBE = 'app.subscribe_to_event';

// DESKPRO WINDOW EVENTS

export const EVENT_DESKPROWINDOW_SHOW_NOTIFICATION = 'deskpro_window.show_notification';

export const EVENT_DESKPROWINDOW_INSERT_MARKUP = 'deskpro_window.insert_markup';

export const EVENT_DESKPROWINDOW_DOM_INSERT = 'deskpro_window.dom_insert';

export const EVENT_DESKPROWINDOW_DOM_QUERY = 'deskpro_window.dom_query';

// USER EVENTS

export const EVENT_ME_GET = 'context.me_get';

export const events =
  {
  // SECURITY EVENTS

    EVENT_SECURITY_AUTHENTICATE_OAUTH,

    EVENT_SECURITY_SETTINGS_OAUTH,

  // API REQUEST EVENTS

    EVENT_WEBAPI_REQUEST_DESKPRO,

    EVENT_WEBAPI_REQUEST_FETCH,

  // TAB EVENTS

    EVENT_TAB_DATA,

    EVENT_TAB_STATUS,

    EVENT_TAB_ACTIVATE,

    EVENT_TAB_CLOSE,

  // TICKET EVENTS

    EVENT_TICKET_REPLY,

  // APP EVENTS

    EVENT_RESET_SIZE,

    EVENT_ME_GET,

    EVENT_SUBSCRIBE,

  // DESKPRO WINDOW EVENTS

    EVENT_DESKPROWINDOW_SHOW_NOTIFICATION,

    EVENT_DESKPROWINDOW_INSERT_MARKUP,

    EVENT_DESKPROWINDOW_DOM_INSERT,

    EVENT_DESKPROWINDOW_DOM_QUERY
  };

export const eventNames = Object.keys(events).map(key => events[key]);

export const isEventName = name => eventNames.indexOf(name) !== -1;

