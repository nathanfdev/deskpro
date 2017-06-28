import { events } from './Events';
import { createDispatchRequestResponse, createDispatchFireAndForget } from './MessageDispatcher';

/**
 * This is the default outgoing request handler. it can be used for logging or any other generic tasks
 *
 * @param {function} send
 * @param {Widget} widget
 * @param {*} message
 * @param {AppServices} services
 * @constructor
 */
export const DEFAULT_HANDLER = (send, widget, message, services) => send(message); // eslint-disable-line no-unused-vars

/**
 * @param {function} send
 * @param {Widget} widget
 * @param {*} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TICKET_REPLY = (send, widget, message, services) => { // eslint-disable-line no-unused-vars
  const { ticket_id, meta } = message; // eslint-disable-line no-unused-vars
  const { api_data, hasBilling, hasTimeLog } = meta;
  send({ api_data, hasBilling, hasTimeLog });
};

export const handlers = {

  // TICKET EVENTS

  EVENT_TICKET_REPLY,

};

export const handlerForEvent = (eventName) => {
  if (eventName === events.EVENT_TICKET_REPLY) {
    return EVENT_TICKET_REPLY;
  }

  return DEFAULT_HANDLER;
};

export const chainHandlers = (firstHandler, secondHandler) => {
  if (typeof firstHandler !== 'function' && typeof secondHandler !== 'function') {
    throw new Error('at least one handler must be a function');
  }

  if (typeof firstHandler !== 'function') {
    return secondHandler;
  }

  if (typeof secondHandler !== 'function') {
    return firstHandler;
  }

  return (send, widget, message, services) => {
    const chain = messageHandledByFirst => secondHandler(send, widget, messageHandledByFirst, services);
    firstHandler(chain, widget, message, services);
  };
};

/**
 * @param {String} eventName
 * @param {EventDispatcher} incomingEventDispatcher
 * @param {Widget} widget
 * @param {AppServices} appServices
 */
export const createListener = ({ eventName, incomingEventDispatcher, widget,  appServices }) =>
  (message, outgoingRequestHandler, incomingResponseHandler) => {
    let send;
    if (incomingResponseHandler) {
      send = createDispatchRequestResponse(eventName, widget, incomingResponseHandler, incomingEventDispatcher);
    } else {
      send = createDispatchFireAndForget(eventName, widget);
    }
    outgoingRequestHandler(send, widget, message, appServices);
  };
