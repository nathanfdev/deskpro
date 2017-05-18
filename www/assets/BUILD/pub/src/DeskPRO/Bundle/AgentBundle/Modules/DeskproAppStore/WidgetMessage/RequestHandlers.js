import { events } from './Events';

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {*} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_STATE_FIND = (response, widget, widgetMessage, services) =>
{
  const { api } = services;
  const { body: state } = widgetMessage;

  api.sendGet(`DP_API/apps/${widget.instanceId}/state`)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) { return httpResponse; }

      if (404 === httpResponse.data.status) { return null; }

      return new Error('failed to get app state');
    })
    .then(data => data instanceof Error ? response(data) : response(null, data))
  ;
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_STATE_GET = (response, widget, widgetMessage, services) =>
{
  const { api } = services;
  const { name, scope } = widgetMessage.body;

  api.sendGet(`DP_API/apps/${widget.instanceId}/state/${name}/${scope}`)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) { return httpResponse; }

      if (404 === httpResponse.data.status) { return null; }

      return new Error('failed to get app state');
    })
    .then(data => data instanceof Error ? response(data) : response(null, data))
  ;
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_STATE_SET = (response, widget, widgetMessage, services) =>
{
  const { api } = services;
  const { name } = widgetMessage.body;
  const { body: state } = widgetMessage;

  api.sendPut(`DP_API/apps/${widget.instanceId}/state/${name}`, state)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) { return httpResponse; }

      // no previous state at that key, let's try and create it
      if (404 === httpResponse.data.status) {
        return api
          .sendPost(`DP_API/apps/${widget.instanceId}/state`, state)
          .then(httpResponse => httpResponse.data)
          .catch(httpResponse => {
            if (httpResponse instanceof Error) { return httpResponse; }

            return new Error('failed to set app state');
          })
        ;
      }
      return new Error('failed to set app state');
    })
    .then(data => data instanceof Error ? response(data) : response(null, data))
  ;
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_STATE_DELETE = (response, widget, widgetMessage, services) =>
{
  const { name } = widgetMessage.body;
  const { api } = services;

  api.sendDelete(`DP_API/apps/${widget.instanceId}/state/${name}`)
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) { return httpResponse; }

      if (404 === httpResponse.data.status) { return null; }

      return new Error('failed to delete app state');
    })
    .then(data => data instanceof Error ? response(data) : response(null, data))
  ;
};


/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_STATUS = (response, widget, widgetMessage, services) => {
  response(null, { active: true });
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_ACTIVATE = (response, widget, widgetMessage, services) => {
  DeskPRO_Window.TabBar.activateTabById(widgetMessage);
  response(null, widgetMessage);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_CLOSE = (response, widget, widgetMessage, services) => {
  DeskPRO_Window.TabBar.removeTabById(widgetMessage);
  response(null, widgetMessage);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_USER_GET = (response, widget, widgetMessage, services) => {
  response(null, { id: services.window.DP_PERSON_ID, email: services.window.DP_PERSON_EMAIL });
};

export const handlers = {

  // STATE EVENT HANDLERS

  EVENT_STATE_FIND,

  EVENT_STATE_GET,

  EVENT_STATE_SET,

  EVENT_STATE_DELETE,

  // TAB EVENTS

  EVENT_TAB_STATUS,

  EVENT_TAB_ACTIVATE,

  EVENT_TAB_CLOSE,

  // USER EVENTS

  EVENT_USER_GET

};

/**
 * @param {EventDispatcher} eventDispatcher
 * @param {String} eventName
 * @param {Function} eventHandler
 * @param {AppServices} appServices
 */
const registerListener = ({ eventDispatcher, eventName, eventHandler, appServices }) => {
  const listener = (response, widget, widgetMessage) => eventHandler(response, widget, widgetMessage, appServices);
  eventDispatcher.addListener(eventName, listener);
  return listener;
};

/**
 * @param {EventDispatcher} eventDispatcher
 * @param {AppServices} appServices
 */
export const registerListeners = (eventDispatcher, appServices) => {
  /** @param {String} key */
  const mapper = key => registerListener({ eventName: events[key], eventHandler: handlers[key], eventDispatcher, appServices });
  return Object.keys(events).map(mapper);
};
