import { createHandlerTrap, createReleaseTrap, createRearmTrap, createIsTrapActive } from '../Services/Interceptors';

import { IncomingEventDispatcher, OutgoingEventDispatcher } from './EventDispatchers';
import { createDispatchResponse } from './MessageDispatcher';

import { registerListeners as registerIncomingRequestListeners  } from './IncomingRequestHandlers';
import {
  createEventSubscriber,
  chainHandlers as chainOutgoingRequestHandlers,
  handlerForEvent as outgoingHandlerForEvent,
  registerHandlers as registerOutgoingRequestListeners
} from './OutgoingRequestHandlers';

import { parseIncomingMessageJS, WidgetResponse, WidgetRequest } from './Message';

export const parseIncomingWidgetMessageJS = parseIncomingMessageJS;

export const addWidgetEventListener = OutgoingEventDispatcher.addRemovableListener.bind(OutgoingEventDispatcher);

/**
 * @param {AppServices} appServices
 */
export const registerIncomingWidgetRequestListeners = appServices => registerIncomingRequestListeners(IncomingEventDispatcher, appServices);

/**
 * @param {AppServices} appServices
 */
export const registerOutgoingWidgetRequestListeners = appServices => registerOutgoingRequestListeners(appServices);

/**
 * @param {String} eventName
 * @param {Widget} widget
 */
export const subscribeWidgetToEvent = (eventName, widget) => {
  const eventSubscriber = createEventSubscriber({
    outgoingEventDispatcher: OutgoingEventDispatcher,
    incomingEventDispatcher: IncomingEventDispatcher
  });

  OutgoingEventDispatcher.emit(`subscribe.${widget.id}`, widget.configuration, eventName, eventSubscriber);
};

// dispatchers

/**
 * Dispatches an incoming message to listening handlers
 *
 * @param {String} eventName
 * @param {WidgetRequest|WidgetResponse} widgetMessage
 * @param {Widget} widget
 */
export const dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget) => {
  if (widgetMessage instanceof WidgetRequest) {
    const callback = createDispatchResponse({
      eventName,
      widget,
      widgetMessage,
      outgoingEventDispatcher: OutgoingEventDispatcher
    });

    IncomingEventDispatcher.emit(eventName, callback, widget, widgetMessage);
    return;
  }

  if (widgetMessage instanceof WidgetResponse) {
    IncomingEventDispatcher.emit(`${eventName}.${widgetMessage.correlationId}`, widget, widgetMessage);
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
