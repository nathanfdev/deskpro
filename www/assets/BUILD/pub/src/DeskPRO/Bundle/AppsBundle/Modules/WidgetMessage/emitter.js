/**
 * this module exports functions which handle outgoing messages, sent from the main window to all the listening apps
 */

import { listenForIncomingResponse, sendRequest, listenForOutgoingMessages, notifyEventSubscribers, cancelIncomingResponseListeners, hasResponseArrived } from './dispatch';
import { createRequest } from './Message';

/**
 * @param {Number} responseCount
 * @param {function} resolve
 * @param {function} reject
 * @return {function(Widget, WidgetResponse)}
 */
function createResponseHandler(responseCount, resolve, reject) { // eslint-disable-line no-unused-vars
  let currentCount = 0;
  const responses = [];

  /**
   * @param {Widget} widget
   * @param {WidgetResponse} message
   * @param {...*} [rest]
   */
  function responseHandler(widget, message, ...rest)  { // eslint-disable-line no-unused-vars
    currentCount++; // eslint-disable-line no-plusplus
    responses.push(message);

    // still waiting for some responses
    if (currentCount < responseCount) {
      return;
    }

    if (currentCount > responseCount) {
      console.warn(`the currentCount (${currentCount}) of widget responses is paradoxically more than those that were sent ${responseCount}`);
    }

    // we received all the responses
    const messages = responses.map(a => a.body);

    if (responses.length === 1) {
      resolve(messages[0]);
    } else {
      resolve([].concat(messages));
    }
  }

  return responseHandler;
}

/**
 * @param {Array<RequestReceipt>} receipts
 * @param {*} message
 * @param {function} resolve
 * @param {function} reject
 * @return {function}
 */
function createTimeoutHandler(receipts, message, resolve, reject) { // eslint-disable-line no-unused-vars
  return function timeoutHandler() {
    let invokeResolve = false;

    for (const receipt of receipts) {
      if (!hasResponseArrived(receipt)) {
        cancelIncomingResponseListeners(receipt);
        invokeResolve = true;
      }
    }

    if (invokeResolve) {
      resolve(message);
    }
  };
}

/**
 * @param {function} resolve
 * @param {function} reject
 * @param {function(function, Number)} setTimeout
 * @param {string} eventName
 * @param {*} message
 */
function emitAsyncHandler(resolve, reject, setTimeout, eventName, message) {
  // let's assume exchangeType = 'request-response'
  /**
   * @var {Array<Widget>}
   */
  const widgets = [];
  notifyEventSubscribers(eventName, (widget) => {
    widgets.push(widget);
  });

  if (widgets.length === 0) {
    resolve(message);
    return;
  }

  const handler = createResponseHandler(widgets.length, resolve, reject);

  const receipts = widgets.map((widget) => {
    const request =  createRequest(widget, message);
    const receipt = listenForIncomingResponse(eventName, widget, request, handler);
    sendRequest(eventName, widget, request);
    return receipt;
  });

  setTimeout(createTimeoutHandler(receipts, message, resolve, reject), 2000);
}

/**
 * Register a listener for all messages that are to be routed to a widget
 *
 * @param {Widget} widget
 * @param {Function} listener
 * @return {Function}
 */
export function registerOutgoingMessageListener(widget, listener) {
  return listenForOutgoingMessages(widget, listener);
}

/**
 * @param {function(function, Number)} setTimeout
 * @param {string} eventName
 * @param {*} message
 * @return {executor}
 */
function createEmitExecutor(setTimeout, eventName, message) {
  /**
   * @param {function} resolve
   * @param {function} reject
   */
  function executor(resolve, reject) {
    try {
      emitAsyncHandler(resolve, reject, setTimeout, eventName, message);
    } catch (e) {
      console.error('app events emitter error ', eventName, message, e);
      reject(e);
    }
  }

  return executor;
}

/**
 * Returns a function that can dispatch a message to all listening widgets
 *
 * @param {function(function, Number)} setTimeout a setTimeout implementation
 * @return {function(string, *): Promise}
 */
export function createEmitAsync(setTimeout) {
  /**
   * @param {string} eventName
   * @param {*} message
   * @return {Promise}
   */
  function emitAsync(eventName, message) {
    return new Promise(createEmitExecutor(setTimeout, eventName, message));
  }

  return emitAsync;
}
