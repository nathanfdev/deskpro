import { default as serializeError } from 'serialize-error';
import { events } from './Events';

/**
 * @param {window} windowObject
 * @param {function} handler
 */
const registerPostMessageListener = (windowObject, handler) => {
  const addListener = windowObject.addEventListener ? windowObject.addEventListener : windowObject.attachEvent;
  const removeListener = windowObject.removeEventListener ? windowObject.removeEventListener : windowObject.detachEvent;
  const event = windowObject.addEventListener ? 'message' : 'onmessage';

  const listener = (e) => {
    const remove = handler(e);
    if (remove) {
      removeListener(event, listener, false);
    }
  };

  addListener(event, listener, false);
};

export const EVENT_SECURITY_SETTINGS_OAUTH = (response, widget, widgetMessage, services) => {
  const { provider, protocolVersion } = widgetMessage.body;

  try {
    const urlRedirect = services.oauthProxy.buildRedirectUrl({
      provider,
      protocolVersion,
      applicationId: widget.instanceId
    }).toString();
    const settings = { urlRedirect };
    response(null, settings);
  } catch (e) {
    response(new Error('failed to build redirect url'), serializeError(e));
  }
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_SECURITY_AUTHENTICATE_OAUTH = (response, widget, widgetMessage, services) => {
  const { correlationId }  = widgetMessage;
  const { id } = widget;

  const { provider, protocolVersion, query } = widgetMessage.body;
  try {
    const authParams = {
      applicationId:  widget.instanceId,
      correlationId,
      callbackMethod: 'postMessage',
      callbackUrl:    services.location.href
    };

    const oauthProxyUrl = services.oauthProxy.buildAuthorizeUrl({ provider, protocolVersion, query })(authParams);
    const listener = services.oauthProxy.buildReceiveTokenListener({ cb: response, protocolVersion, oauthProxyUrl })(authParams);

    const windowName = `auth-${id}-${provider}`;
    const windowFeatures = ['width=500,height=500,left=500,top=10', 'status=yes'].join(',');

    registerPostMessageListener(services.window, listener);
    services.window.open(oauthProxyUrl, windowName, windowFeatures);
  } catch (e) {
    response(new Error('failed to authenticate'), serializeError(e));
  }
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_SECURITY_OAUTH_REFRESH = (response, widget, widgetMessage, services) => {
  const { correlationId }  = widgetMessage;

  const { provider, protocolVersion, query } = widgetMessage.body;
  try {
    const authParams = {
      applicationId: widget.instanceId,
      correlationId
    };

    const oauthProxyUrl = services.oauthProxy.buildRefreshAccessUrl({ protocolVersion, provider, query })(authParams);
    services.getDeskproAPIClient({ allowAbsoluteUrls: true }).fetch(oauthProxyUrl, { method: 'GET' })
      .then((httpResponse) => {
        const headers = httpResponse.getAllHeadersMap();
        const data = {
          status:     httpResponse.status,
          body:       httpResponse.data,
          headers,
          statusCode: httpResponse.getResponseCode()
        };
        response(null, data);
        return httpResponse;
      })
      .catch((httpResponse) => {
        const headers = httpResponse.getAllHeadersMap();
        const data = httpResponse instanceof Error ? null : {
          status:     httpResponse.status,
          body:       httpResponse.data,
          headers,
          statusCode: httpResponse.getResponseCode()
        };
        const requestError = httpResponse instanceof Error ? httpResponse : new Error('[API] Failed to execute request');
        response(requestError, data);

        return httpResponse;
      });
  } catch (e) {
    response(new Error('failed to authenticate'), serializeError(e));
  }
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_WEBAPI_REQUEST_FETCH = (response, widget, widgetMessage, services) => {
  const normalizeRequest = (request) => {
    const { url, init } = request;
    const { method, path, body } = request;

    if (method && path) {
      return body ? { url: path, init: { method, body } } : { url: path, init: { method } };
    }

    return { url, init };
  };

  const { body: request } = widgetMessage;
  const normalizedRequest = normalizeRequest(request);
  const { url, init } = normalizedRequest;

  const isExternalRequest = url.match(/^(?:[a-z]+:)?\/\//i);

  // validate request
  let errorMessage;
  const dpAPIAllowedMethods = ['get', 'post', 'put', 'delete'];
  if (init.mode !== 'cors' && dpAPIAllowedMethods.indexOf(init.method.toString().toLowerCase()) === -1) {
    errorMessage = `[API]: Method not allowed ${init.method}. Allowed methods are: ${dpAPIAllowedMethods.join(', ')}`;
  } else if (isExternalRequest && init.mode !== 'cors') {
    errorMessage = '[API]: External requests must use CORS mode';
  }

  if (errorMessage) {
    response(new Error(errorMessage));
    return;
  }

  const fetchClient = isExternalRequest ? services.getProxyClient({ widget }) : services.dpClient;

  fetchClient.fetch(url, init)
    .then((httpResponse) => {
      const headers = httpResponse.getAllHeadersMap();
      const data = {
        status:     httpResponse.status,
        body:       httpResponse.data,
        headers,
        statusCode: httpResponse.getResponseCode()
      };
      response(null, data);
      return httpResponse;
    })
    .catch((httpResponse) => {
      const headers = httpResponse.getAllHeadersMap();
      const data = httpResponse instanceof Error ? null : {
        status:     httpResponse.status,
        body:       httpResponse.data,
        headers,
        statusCode: httpResponse.getResponseCode()
      };
      const requestError = httpResponse instanceof Error ? httpResponse : new Error('[API] Failed to execute request');
      response(requestError, data);

      return httpResponse;
    });
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices}  services
 * @constructor
 */
export const EVENT_WEBAPI_REQUEST_DESKPRO = (response, widget, widgetMessage, services) => {
  const { body: invocation } = widgetMessage;

  let error;
  const allowedMethods = ['get', 'post', 'put', 'delete'];

  const { method, path, body } = invocation;

  if (path.match(/^(?:[a-z]+:)?\/\//i)) {
    error = new Error(`[API]: Invalid path: ${path}. Absolute paths are not allowed`);
  }  else if (allowedMethods.indexOf(method.toLowerCase()) === -1) {
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
    default:
      throw new Error(`failed to execute fetch: unknown method ${method}`);
  }

  requestPromise
    .then((httpResponse) => {
      const headers = httpResponse.getAllHeadersMap();
      const data = { status: httpResponse.status, body: httpResponse.data, headers };
      response(null, data);

      return httpResponse;
    })
    .catch((httpResponse) => {
      const headers = httpResponse.getAllHeadersMap();
      const data = httpResponse instanceof Error ? null : { status: httpResponse.status, body: httpResponse.data, headers };
      const requestError = httpResponse instanceof Error ? httpResponse : new Error('[API] Failed to execute request');
      response(requestError, data);

      return httpResponse;
    })
  ;
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_CONTEXT_PROPERTY_GET = (response, widget, widgetMessage, services) => {
  const { tabId, path } = widgetMessage.body;
  const tab = services.tabs.getTab(tabId);
  if (!tab) {
    return response(new Error('tab not found'), { tabId, path });
  }

  let invalidProperty = false;
  if (path instanceof Array) {
    invalidProperty = path.filter(segment => !(typeof segment === 'string' && segment.length > 0)).length > 0;
  } else {
    invalidProperty = true;
  }

  if (invalidProperty) {
    return response(new Error('property invalid'), { tabId, path });
  }

  const { api_v2_data } = tab.page.meta;

  if (path.length === 0) {
    return response(null, api_v2_data);
  }

  const error = {};
  const reducer = (acc, segment) => {
    if (acc === error) {
      return error;
    }

    if (acc && typeof acc === 'object') {
      return acc[segment];
    }

    return error;
  };
  const extractedProperty = path.reduce(reducer, api_v2_data);

  if (extractedProperty === error || extractedProperty === undefined) {
    return response(new Error('property not found'), { tabId, path });
  }

  return response(null, extractedProperty);
};

/**
 *
 * This event is now deprecated and should only be used for internal purposes
 * @deprecated
 *
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_DATA = (response, widget, widgetMessage, services) => {
  const { body: tabId } = widgetMessage;
  const tab = services.tabs.getTab(tabId);
  if (!tab) {
    return response(new Error('tab not found'), tabId);
  }

  const { api_data, hasBilling, hasTimeLog } = tab.page.meta;
  return response(null, { api_data, hasBilling, hasTimeLog });
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_STATUS = (response, widget, widgetMessage, services) => { // eslint-disable-line no-unused-vars
  response(null, { active: true });
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_ACTIVATE = (response, widget, widgetMessage, services) => {
  const { body: tabId } = widgetMessage;
  services.tabs.activateTabById(tabId);
  response(null, tabId);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TAB_CLOSE = (response, widget, widgetMessage, services) => {
  const { body: tabId } = widgetMessage;
  services.tabs.removeTabById(tabId);
  response(null, tabId);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} widgetMessage
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_ME_GET = (response, widget, widgetMessage, services) => {
  response(null, services.authUser);
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_RESET_SIZE = (response, widget, message, services) => {
  const { size } = message.body;
  const height = size.outerHeight + 20;

  try {
    const { widgetDOM, $ } = services;
    const iframe = widgetDOM.findIframe(widget);
    $(iframe).height(height);

    response(null, { height });
  } catch (e) {
    console.error('app reset size failed', e);
    response(e);
  }
};

// DESKPRO WINDOW EVENT HANDLERS

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_DESKPROWINDOW_SHOW_NOTIFICATION = (response, widget, message, services) => {
  const { body: notification } = message;
  if (typeof notification === 'string') {
    services.showNotification(notification);
  }
};

/**
 * @param {function} response
 * @param {Widget} widget
 * @param {WidgetRequest} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_DESKPROWINDOW_INSERT_MARKUP = (response, widget, message, services) => {
  const { body: markup } = message;
  const { $, window } = services;

  try {
    if (markup instanceof Array) {
      markup.forEach((markupFragment) => {
        $(window.document.body).append(markupFragment);
      });
    } else if (typeof markup === 'string') {
      $(window.document.body).append(markup);
    }
    response(null, markup);
  } catch (e) {
    response(e);
  }
};

export const EVENT_DESKPROWINDOW_DOM_INSERT = (response, widget, message, services) => {
  const { parent, markup } = message.body;

  const { $, window } = services;

  try {
    const parentEl = typeof parent === 'string' ? $(parent) : $(window.document.body);
    if (parentEl.length) {
      parentEl.append(markup);
      response(null, markup);
    } else {
      response(new Error('could not find parent element for insertion'), { ...message.body });
    }
  } catch (e) {
    response(e);
  }
};


export const EVENT_DESKPROWINDOW_DOM_QUERY = (response, widget, message, services) => {
  const { $ } = services;

  let patterns;
  if (!(message.body instanceof Array)) {
    patterns = [message.body];
  } else {
    patterns = message.body.concat([]);
  }

  const evaluator = (pattern) => {
    // send back unrecognized objects
    if (typeof pattern !== 'object') {
      return pattern;
    }

    const { type, selector } = pattern;
    if (typeof type === 'string' && type === 'valueOf') {
      const value = $(selector).val();
      return { ...pattern, value };
    } else if (typeof type === 'string' && type === 'exists') {
      const exists = $(selector).length > 0;
      return { ...pattern, exists };
    }

    return pattern;
  }
  ;

  try {
    patterns = patterns.map(evaluator);
    if (message.body instanceof Array) {
      response(null, patterns);
    } else {
      response(null, patterns.pop());
    }
  } catch (e) {
    response(e);
  }
};

export const handlers = {

  // SECURITY EVENT HANDLERS

  EVENT_SECURITY_AUTHENTICATE_OAUTH,

  EVENT_SECURITY_OAUTH_REFRESH,

  EVENT_SECURITY_SETTINGS_OAUTH,

  // GENERIC REST API REQUEST EVENT

  EVENT_WEBAPI_REQUEST_DESKPRO,

  EVENT_WEBAPI_REQUEST_FETCH,

  // CONTEXT EVENTS

  EVENT_CONTEXT_PROPERTY_GET,

  // TAB EVENTS

  EVENT_TAB_DATA,

  EVENT_TAB_STATUS,

  EVENT_TAB_ACTIVATE,

  EVENT_TAB_CLOSE,

  // USER EVENTS

  EVENT_ME_GET,

  // APP EVENTS

  EVENT_RESET_SIZE,

  // DESKPRO WINDOW EVENTS

  EVENT_DESKPROWINDOW_SHOW_NOTIFICATION,

  EVENT_DESKPROWINDOW_INSERT_MARKUP,

  EVENT_DESKPROWINDOW_DOM_INSERT,

  EVENT_DESKPROWINDOW_DOM_QUERY
};


/**
 * @param {Object} appServices
 * @param {Function} handler
 * @return {function(Function, Widget, (WidgetRequest|WidgetResponse)): function(Function, Widget, (WidgetRequest|WidgetResponse), Object)}
 */
function createListener(appServices, handler) {
  /**
   * @param {function} response
   * @param {Widget} widget
   * @param {WidgetRequest|WidgetResponse} widgetRequest
   * @return {function}
   */
  function listener(response, widget, widgetRequest)  {
    return handler(response, widget, widgetRequest, appServices);
  }

  return listener;
}

/**
 * Returns a map of event name and listener
 *
 * @param {object} appServices
 * @return {{}}
 */
export default function bindIncomingMessageHandlers(appServices) {
  function reducer(acc, key)  {
    const eventName = events[key];
    acc[eventName] = createListener(appServices, handlers[key]);
    return acc;
  }

// intersect the handlers with events, picking entries which exist in both maps;
  return Object.keys(handlers).filter(key => Object.prototype.hasOwnProperty.call(events, key)).reduce(reducer, {});
}
