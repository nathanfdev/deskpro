import { EventEmitter } from 'eventemitter3';
import { createHandlerTrap, createReleaseTrap } from '../Services/Interceptors';

export class EventDispatcher extends EventEmitter {}

export const IncomingEventDispatcher = new EventDispatcher();
export const OutgoingEventDispatcher = new EventDispatcher();

/**
 * Creates a function that intercepts invocations of a handler function, dispatches an event to all interested widgets
 * and invokes the onResponse callback with the widget's response
 *
 * @param {String} eventName
 * @param {function} onResponse
 * @param {function} onActivate
 * @param {function} handler
 * @return {function(...[*])}
 */
const createInterceptor = (eventName, onResponse, onActivate, handler) => { // eslint-disable-line no-unused-vars
  const handlerTrap = createHandlerTrap(handler);
  const releaseTrap = createReleaseTrap(handlerTrap);

  const state = { onActivateActive: true };

  const interceptor = (...args) => {
    const { onActivateActive } = state;
    if (onActivateActive) {
      state.onActivateActive = false;

      const message = typeof onActivate === 'function' ? onActivate(...args) : onActivate;
      OutgoingEventDispatcher.emit(eventName, message);
    }

    handlerTrap(...args);
  };

  const incomingListener = (widget, message) => onResponse(message, releaseTrap);
  IncomingEventDispatcher.once(eventName, incomingListener);

  return interceptor;
};
