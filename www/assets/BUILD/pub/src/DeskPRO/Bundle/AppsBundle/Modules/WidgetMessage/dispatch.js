import EventDispatcher from './EventDispatcher';
import { createErrorResponse, createSuccessResponse } from './Message';

// TODO: namespace events so we can use a single instance of EventDispatcher
const receiverDispatcher = new EventDispatcher();
const emitterDispatcher = new EventDispatcher();
const eventSubscribers = new EventDispatcher();

export function notifyEventSubscribers(event, ...args) {
  eventSubscribers.emit(event, ...args);
}

export function registerEventSubscriber(event, handler) {
  return eventSubscribers.addRemovableListener(event, handler);
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} request
 */
export function sendRequest(eventName, widget, request) {
  emitterDispatcher.emit(widget.id, widget.configuration, eventName, request);
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetResponse} response
 */
export function sendResponse(eventName, widget, response) {
  emitterDispatcher.emit(widget.id, widget.configuration, eventName, response);
}

/**
 * @typedef {Object} RequestReceipt
 * @property {string} token
 * @property {string} channel
 */

/**
 * @param {RequestReceipt} receipt
 * @return {boolean}
 */
export function hasResponseArrived(receipt) {
  const { channel, token } = receipt;
  if (channel === 'outgoing') {
    return !emitterDispatcher.listeners(token, true);
  }

  if (channel === 'incoming') {
    return !receiverDispatcher.listeners(token, true);
  }

  throw new Error('unknown channel');
}

/**
 * @param {RequestReceipt} receipt
 */
export function cancelIncomingResponseListeners(receipt) {
  const { channel, token } = receipt;
  if (channel === 'outgoing') {
    return emitterDispatcher.removeAllListeners(token);
  }

  if (channel === 'incoming') {
    return receiverDispatcher.removeAllListeners(token);
  }

  throw new Error('unknown channel');
}

/**
 * @param {Widget} widget
 * @param {Function} handler
 * @return {Function}
 */
export function listenForOutgoingMessages(widget, handler) {
  return emitterDispatcher.addRemovableListener(widget.id, handler);
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} request
 * @param {Function} handler
 * @return {RequestReceipt}
 */
export function listenForIncomingResponse(eventName, widget, request, handler) {
  const registrationId = `${eventName}.${request.correlationId}`;
  receiverDispatcher.once(registrationId, handler);

  return { token: registrationId, channel: 'incoming' };
}

/**
 * @param {string} eventName
 * @param {Function} handler
 * @return {Function}
 */
export function listenForIncomingRequest(eventName, handler) {
  return receiverDispatcher.addRemovableListener(eventName, handler);
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} request
 */
export function interceptRequest(eventName, widget, request) {
  function callback(err, data) {
    const response = err ? createErrorResponse(request, err, data) : createSuccessResponse(request, data);
    sendResponse(eventName, widget, response);
  }

  return function emit(emitter) {
    emitter(callback, widget, request);
  };
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} request
 */
export function receiveRequest(eventName, widget, request) {
  function callback(err, data) {
    const response = err ? createErrorResponse(request, err, data) : createSuccessResponse(request, data);
    sendResponse(eventName, widget, response);
  }

  receiverDispatcher.emit(eventName, callback, widget, request);
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetResponse} response
 */
export function interceptResponse(eventName, widget, response) {
  return function emit(emitter) {
    emitter(widget, response);
  };
}

/**
 * @param {string} eventName
 * @param {Widget} widget
 * @param {WidgetResponse} response
 */
export function receiveResponse(eventName, widget, response) {
  receiverDispatcher.emit(`${eventName}.${response.correlationId}`, widget, response);
}

