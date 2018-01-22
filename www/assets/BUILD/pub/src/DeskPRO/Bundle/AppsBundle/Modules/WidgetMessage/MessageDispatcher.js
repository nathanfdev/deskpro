import { createErrorResponse, createSuccessResponse, createRequest } from './Message';

/**
 * Creates a function that dispatches a response to the ${widgetRequest} widget request
 *
 * @param {String} eventName
 * @param {Widget} widget
 * @param {EventEmitter} outgoingEventDispatcher
 * @param {WidgetRequest} widgetMessage
 */
export const createDispatchResponse = ({
  eventName,
  widget,
  widgetMessage,
  outgoingEventDispatcher
}) => (err, data) => {
  const widgetResponse = err ? createErrorResponse(widgetMessage, err, data) : createSuccessResponse(widgetMessage, data);
  // send a notification to dispatch the message to the widget
  outgoingEventDispatcher.emit(widget.id, widget.configuration, eventName, widgetResponse);
};

/**
 * Creates a function that dispatches a widget request which must be followed by a response
 *
 * @param {String} eventName
 * @param {Widget} widget
 * @param {EventEmitter} outgoingEventDispatcher
 * @param {function} incomingResponseHandler
 * @param {EventEmitter} incomingEventDispatcher
 */
export const createDispatchRequestResponse = ({
  eventName,
  widget,
  outgoingEventDispatcher,
  incomingResponseHandler,
  incomingEventDispatcher
}) => (message) => {
  const request = createRequest(widget, message);
    // register a response listener
  incomingEventDispatcher.once(`${eventName}.${request.correlationId}`, incomingResponseHandler);
    // send a notification to dispatch the message to the widget
  outgoingEventDispatcher.emit(widget.id, widget.configuration, eventName, request);
};

/**
 * Creates a function that dispatches a widget request which must not be followed by a response
 *
 * @param {String} eventName
 * @param {Widget} widget
 * @param {EventEmitter} outgoingEventDispatcher
 */
export const createDispatchFireAndForget = ({
  eventName,
  widget,
  outgoingEventDispatcher
}) => (message) => {
  const request = createRequest(widget, message);
  // send a notification to dispatch the message to the widget
  outgoingEventDispatcher.emit(widget.id, widget.configuration, eventName, request);
};
