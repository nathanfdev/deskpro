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

const registeredHandlers = {};

export const handlerForEvent = (eventName) => {
  const handlerRegistered = Object.prototype.hasOwnProperty.call(registeredHandlers, eventName);
  const handlerKey =  handlerRegistered ? eventName : '__DEFAULT__';

  return registeredHandlers[handlerKey];
};

const registerHandler = (eventName, handler, appServices) => {
  registeredHandlers[eventName] = (response, widget, message) => handler(response, widget, message, appServices);
};

export const registerHandlers = (appServices) => {
  if (Object.keys(registeredHandlers).length > 0) { return false; }

  Object.keys(handlers).forEach((key) => {
    const eventName = events[key];
    registerHandler(eventName, handlers[key], appServices);
  });
  registerHandler('__DEFAULT__', DEFAULT_HANDLER, appServices);
  return true;
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
 * @param {EventDispatcher} outgoingEventDispatcher
 * @return {function}
 */
const createListener = ({
  eventName,
  widget,
  incomingEventDispatcher,
  outgoingEventDispatcher
}) => (message, outgoingRequestHandler, incomingResponseHandler) => {
  let send;
  if (incomingResponseHandler) {
    send = createDispatchRequestResponse({
      eventName,
      widget,
      outgoingEventDispatcher,
      incomingResponseHandler,
      incomingEventDispatcher,
    });
  } else {
    send = createDispatchFireAndForget({ eventName, widget, outgoingEventDispatcher });
  }

  outgoingRequestHandler(send, widget, message);
};

/**
 * @param {EventDispatcher} incomingEventDispatcher
 * @param {EventDispatcher} outgoingEventDispatcher
 */
export const createEventSubscriber = ({ incomingEventDispatcher, outgoingEventDispatcher }) => (eventName, widget) => {
  const listener = createListener({
    eventName,
    widget,
    incomingEventDispatcher,
    outgoingEventDispatcher,
  });

  return outgoingEventDispatcher.addRemovableListener(eventName, listener);
};
