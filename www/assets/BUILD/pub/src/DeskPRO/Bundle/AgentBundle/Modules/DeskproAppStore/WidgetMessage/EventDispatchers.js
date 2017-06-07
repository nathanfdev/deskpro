import { EventEmitter } from 'eventemitter3';
import {createHandlerTrap, createReleaseTrap } from '../Services/Interceptors';

export class EventDispatcher extends EventEmitter
{}

export const IncomingEventDispatcher = new EventDispatcher();
export const OutgoingEventDispatcher = new EventDispatcher();


const createInterceptor = (eventName, onResponse, onActivate, handler) => {
  const handlerTrap = createHandlerTrap(handler);
  const releaseTrap = createReleaseTrap(handlerTrap);

  const state = { onActivateActive: true };

  const interceptor = (...args) => {
    const { onActivateActive } = state;
    if (onActivateActive) {
      state.onActivateActive = false;

      const message = typeof onActivate === 'function' ? onActivate.apply(null, args) : onActivate;
      OutgoingEventDispatcher.emit(eventName, message)
    }

    handlerTrap.apply(null, args);
  };

  const incomingListener = (widget, message) => onResponse(message, releaseTrap);
  IncomingEventDispatcher.once(eventName, incomingListener);

  return interceptor;
};
