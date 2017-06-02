import { events } from './Events';

export const EVENT_WEBAPI_REQUEST_DESKPRO = (response, widget, widgetMessage, services) =>
{
  const { body: invocation } = widgetMessage;

  let error;
  const allowedMethods = ['get', 'post', 'put', 'delete'];

  const { method, path, body } = invocation;

  if (path.match(/^(?:[a-z]+:)?\/\//i)) {
    error = new Error(`[API]: Invalid path: ${path}. Absolute paths are not allowed`);
  }
  else if ( allowedMethods.indexOf(method.toLowerCase()) == -1 ) {
    error = new Error(`[API]: Method not allowed ${method}. Allowed methods are: ${allowedMethods.join(', ')}`);
  }

  // TODO maybe check the body is not undefined or null for post put

  if (error) {
    response(error);
    return;
  }

  const { api } = services;
  const apiEndpoint = ['DP_API', path].join('/');
  let requestPromise;

  switch (method.toLowerCase()) {
    case 'get':
      requestPromise = api.sendGet(apiEndpoint);
      break;
    case 'post':
      requestPromise = api.sendPost(apiEndpoint, body);
      break;
    case 'put':
      requestPromise = api.sendPut(apiEndpoint, body);
      break;
    case 'delete':
      requestPromise = api.sendDelete(apiEndpoint);
      break;
  }

  requestPromise
    .catch(httpResponse => {

      const data = httpResponse instanceof Error ? null : { status: httpResponse.status, body: httpResponse.data };
      const error = httpResponse instanceof Error ? httpResponse : new Error('[API] Failed to execute request');
      response(error, data);

      return httpResponse;
    })
    .then(httpResponse => {
      const data = { status: httpResponse.status, body: httpResponse.data };
      response(null, data);

      return httpResponse;
    })
  ;
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
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

  api.sendGet(`DP_API/apps/${widget.instanceId}/state/${name}/${scope}?mode=find`)
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
  const { name, scope } = widgetMessage.body;
  const { body: state } = widgetMessage;

  api.sendHead(`DP_API/apps/${widget.instanceId}/state/${name}/${scope}`)
    .then(httpResponse => {
      if (204 === httpResponse.getResponseCode()) {
        return api.sendPost(`DP_API/apps/${widget.instanceId}/state`, state);
      } else if (200 === httpResponse.getResponseCode()) {
        return api.sendPut(`DP_API/apps/${widget.instanceId}/state/${name}/${scope}`, state);
      }

      throw new Error('could not save state');
    })
    .then(httpResponse => httpResponse.data)
    .catch(httpResponse => {
      if (httpResponse instanceof Error) { return httpResponse; }

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
export const EVENT_STATE_DELETE = (response, widget, widgetMessage, services) =>
{
  const { name, scope } = widgetMessage.body;
  const { api } = services;

  api.sendDelete(`DP_API/apps/${widget.instanceId}/state/${name}/${scope}`)
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
export const EVENT_TAB_DATA = (response, widget, widgetMessage, services) => {
  const { body: tabId } = widgetMessage;
  const tab = DeskPRO_Window.TabBar.getTab(tabId);
  if (! tab) {
    return response(new Error('tab not found'), tabId);
  }

  const { api_data } = tab.page.meta;
  response(null, { api_data });
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
  const { body: tabId } = widgetMessage;
  DeskPRO_Window.TabBar.activateTabById(tabId);
  response(null, tabId);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_CLOSE = (response, widget, widgetMessage, services) => {
  const { body: tabId } = widgetMessage;
  DeskPRO_Window.TabBar.removeTabById(tabId);
  response(null, tabId);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_ME_GET = (response, widget, widgetMessage, services) =>
{
  response(null, { id: services.window.DP_PERSON_ID, email: services.window.DP_PERSON_EMAIL });
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetMessage} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_RESET_SIZE = (response, widget, message, services) => {
  const { size } = message.body;
  const height = size.outerHeight + 20 /* i dont know why +20? */;

  try {
    const { widgetDOM, $ } = services;
    const iframe = widgetDOM.findIframe(widget);
    $(iframe).height(height);

    response(null, { height })
  } catch (e) {
    console.log('app reset size failed', e);
    response(e);
  }

};

export const handlers = {

  // GENERIC REST API REQUEST EVENT

  EVENT_WEBAPI_REQUEST_DESKPRO,

  // STATE EVENT HANDLERS

  EVENT_STATE_FIND,

  EVENT_STATE_GET,

  EVENT_STATE_SET,

  EVENT_STATE_DELETE,

  // TAB EVENTS

  EVENT_TAB_DATA,

  EVENT_TAB_STATUS,

  EVENT_TAB_ACTIVATE,

  EVENT_TAB_CLOSE,

  // USER EVENTS

  EVENT_ME_GET,

  // APP EVENTS

  EVENT_RESET_SIZE
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
