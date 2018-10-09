/**
 * this module exports functions which handle incoming messages
 */

import { parseIncomingMessageJS, createSuccessResponse, WidgetResponse, WidgetRequest } from './Message';
import { listenForIncomingRequest, receiveRequest, receiveResponse, interceptRequest, interceptResponse, sendResponse, registerEventSubscriber } from './dispatch';

import { EVENT_SUBSCRIBE } from './Events';

/**
 * @param {Object} listeners
 */
export function registerIncomingRequestListeners(listeners) {
  const reducer = (acc, eventName) =>  {
    const listener = listeners[eventName];
    acc[eventName] = listenForIncomingRequest(eventName, listener);
    return acc;
  };

  return Object.keys(listeners).reduce(reducer, {});
}

/**
 * Registers a widget subscription for an event
 *
 * @param {Widget} widget
 * @param {{data: Object}} event
 * @param {Context} context the context for the widget from where the events will be emitted
 */
export function receiveSubscription(widget, event, context) {
  const { eventName } = event.data;

  if (EVENT_SUBSCRIBE !== eventName) {
    throw new Error(`unexpected event name. expecting ${EVENT_SUBSCRIBE}, received ${eventName}`);
  }

  const { events  } = event.data.body; // eslint-disable-line no-shadow
  if (!(events instanceof Array)) {
    throw new Error('expecting a list of event names');
  }

  function subscriber(cb) {
    cb(widget);
  }

  const request = parseIncomingMessageJS(event.data);

  // let's assume that all events consumed by apps are of invocationType = event.invocation_requestresponse request-response
  // the other possibility is invocationType = event.invocation_fireandforget, example
  //
  // const availableEvents = [
  //   {
  //     name:           'context.ticket.reply-success',
  //     invocationType: 'event.invocation_fireandforget'
  //   },
  //   {
  //     name:           'context.ticket.update-success',
  //     invocationType: 'event.invocation_fireandforget'
  //   }
  // ];

  const eventConfigurations = events.map(name => ({
    name,
    invocationType: 'event.invocation_requestresponse'
  }));

  const response = createSuccessResponse(request, eventConfigurations);
  sendResponse(eventName, widget, response);

  return events.reduce((acc, name) => {
    // let's namespace this event so we don't send events to all the widgets instead of those in the same context
    const contextName = [name, context.tabId].join('.');
    acc[name] = registerEventSubscriber(contextName, subscriber);
    return acc;
  }, {});
}

/**
 * Dispatches a message initiated by a widget to registered listeners
 *
 * @param {Widget} widget
 * @param {{data: Object}} event
 * @return {function}
 */
export function interceptMessage(widget, event) {
  const { eventName } = event.data;

  if (!eventName) {
    throw new Error('failed to dispatch incoming message: unrecognized event name');
  }

  const message = parseIncomingMessageJS(event.data);

  if (message instanceof WidgetRequest) {
    return interceptRequest(eventName, widget, message);
  }

  if (message instanceof WidgetResponse) {
    return interceptResponse(eventName, widget, message);
  }

  throw new Error('failed to dispatch incoming message: could not parse widget message');
}

/**
 * Dispatches a message initiated by a widget to registered listeners
 *
 * @param {Widget} widget
 * @param {{data: Object}} event
 */
export function receiveMessage(widget, event) {
  const { eventName } = event.data;

  if (!eventName) {
    throw new Error('failed to dispatch incoming message: unrecognized event name');
  }

  const message = parseIncomingMessageJS(event.data);

  if (message instanceof WidgetRequest) {
    receiveRequest(eventName, widget, message);
    return null;
  }

  if (message instanceof WidgetResponse) {
    receiveResponse(eventName, widget, message);
    return null;
  }

  throw new Error('failed to dispatch incoming message: could not parse widget message');
}
