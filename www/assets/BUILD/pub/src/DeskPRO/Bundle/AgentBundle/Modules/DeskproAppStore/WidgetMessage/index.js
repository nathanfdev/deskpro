import { createHandlerTrap, createReleaseTrap, createRearmTrap, createIsTrapActive } from '../Services/Interceptors';

import { IncomingEventDispatcher, OutgoingEventDispatcher } from './EventDispatchers';
import { createDispatchResponse } from './MessageDispatcher';

import { registerListeners as registerIncomingRequestListeners  } from './IncomingRequestHandlers';
import { createListener as createOutgoingListener, chainHandlers as chainOutgoingRequestHandlers, handlerForEvent as outgoingHandlerForEvent  } from './OutgoingRequestHandlers';

import { parseIncomingMessageJS, WidgetResponse, WidgetRequest } from './Message';

/**
 * @param {AppServices} appServices
 */
export const registerIncomingWidgetRequestListeners = (appServices) => {
  const result = registerIncomingRequestListeners(IncomingEventDispatcher, appServices);
  return result;
};

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {AppServices} appServices
 */
export const addWidgetEventListener = (eventName, widget, appServices) => {
  const listener = createOutgoingListener({
    eventName,
    widget,
    appServices,
    incomingEventDispatcher: IncomingEventDispatcher
  });

  OutgoingEventDispatcher.addListener(eventName, listener);
};

// dispatchers

/**
 * Dispatches an incoming message to listening handlers
 *
 * @param {String} eventName
 * @param {*} widgetMessage
 * @param {Widget} widget
 */
export const dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget) => {
  // parse message
  const message = parseIncomingMessageJS(widgetMessage);

  if (message instanceof WidgetRequest) {
    const callback = createDispatchResponse(eventName, widget, message);
    IncomingEventDispatcher.emit(eventName, callback, widget, message);
    return;
  }

  if (message instanceof WidgetResponse) {
    IncomingEventDispatcher.emit(`${eventName}.${message.correlationId}`, widget, message);
  }
};

/**
 * @param {String} eventName
 * @param {*} message
 * @param {function|null} outgoingRequestHandler
 * @param {function|null} incomingResponseHandler
 */
export const dispatchOutgoingWidgetMessage = (eventName, message, outgoingRequestHandler, incomingResponseHandler) => {
  const defaultOutgoingRequestHandler = outgoingHandlerForEvent(eventName);
  const handler = chainOutgoingRequestHandlers(outgoingRequestHandler, defaultOutgoingRequestHandler);

  OutgoingEventDispatcher.emit(eventName, message, handler, incomingResponseHandler);
};

const hasOutgoingListeners = eventName => OutgoingEventDispatcher.listeners(eventName, true);

/**
 * @param {String} eventName
 * @param {function} onResponse
 * @param {function|*} onActivate
 * @param {function} intercepted
 * @return {function(...[*]=)}
 */
export const dispatchOutgoingWidgetRequestOnIntercept = (eventName, onResponse, onActivate, intercepted) => {
  const trap = createHandlerTrap(intercepted);
  const releaseTrap = createReleaseTrap(trap);
  const rearmTrap = createRearmTrap(trap);
  const isTrapActive = createIsTrapActive(trap);

  const trapHandler = (release) => {
    if (release) { releaseTrap(); } else { rearmTrap(); }
    return release;
  };
  const incomingResponseListener = (widget, message) => onResponse(trapHandler, widget, message);

  return (...args) => {
    const active = isTrapActive();
    const dispatchOutgoingMessage = active && hasOutgoingListeners(eventName);

    if (dispatchOutgoingMessage) {
      const message = typeof onActivate === 'function' ? onActivate(...args) : onActivate;
      dispatchOutgoingWidgetMessage(eventName, message, null, incomingResponseListener);
    }

    trap(...args);

    // if trap was active before applying and we did not dispatch the outgoing message and now the trap is inactive release it
    if (active && !dispatchOutgoingMessage && !isTrapActive()) { releaseTrap(); }
  };
};
