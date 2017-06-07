import postRobot from 'post-robot/dist/post-robot.js';

import * as WidgetDOM from '../WidgetDOM';
import { createErrorResponse, createSuccessResponse, createRequest, WidgetRequest } from './Message'

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} widgetRequest
 */
export const createDispatchResponse = (eventName, widget, widgetRequest) => (err, data) => {
  const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
  if (! widgetWindow) {
    throw new Error('can not find widget window');
  }
  const widgetResponse = err ? createErrorResponse(widgetRequest, err) : createSuccessResponse(widgetRequest, data);
  postRobot.send(widgetWindow, eventName, widgetResponse.toJS());
};

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {function} incomingResponseHandler
 * @param {EventEmitter} eventDispatcher
 */
export const createDispatchRequestResponse = (eventName, widget, incomingResponseHandler, eventDispatcher) => message => {
  const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
  if (! widgetWindow) { // TODO handle widget unloading and remove event listeners
    const error = new Error('can not find widget window');
    console.log(error);
    return ;
  }

  const request = createRequest(widget, message);
  // register a response listener
  eventDispatcher.once(`${eventName}.${request.correlationId}`, incomingResponseHandler);
  postRobot.send(widgetWindow, eventName, request.toJS());
};

/**
 * @param {String} eventName
 * @param {Widget} widget
 */
export const createDispatchFireAndForget = (eventName, widget) => message => {
  const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
  if (! widgetWindow) { // TODO handle widget unloading and remove event listeners
    const error = new Error('can not find widget window');
    console.log(error);
    return ;
  }

  const request = createRequest(widget, message);
  postRobot.send(widgetWindow, eventName, request.toJS());
};
