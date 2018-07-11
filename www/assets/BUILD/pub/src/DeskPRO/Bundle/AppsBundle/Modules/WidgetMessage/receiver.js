/**
 * this module exports functions which handle incoming messages
 */

import { parseIncomingMessageJS, createSuccessResponse, WidgetResponse, WidgetRequest } from './Message';
import { listenForIncomingRequest, receiveRequest, receiveResponse, sendResponse, registerEventSubscriber } from './dispatch';

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
 */
export function receiveSubscription(widget, event) {
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
    acc[name] = registerEventSubscriber(name, subscriber);
    return acc;
  }, {});
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

  const widgetMessage = parseIncomingMessageJS(event.data);

  if (widgetMessage instanceof WidgetRequest) {
    receiveRequest(eventName, widget, widgetMessage);
    return null;
  }

  if (widgetMessage instanceof WidgetResponse) {
    receiveResponse(eventName, widget, widgetMessage);
    return null;
  }

  throw new Error('failed to dispatch incoming message: could not parse widget message');
}
