import postRobot from 'post-robot/dist/post-robot.js';

import * as WidgetDOM from '../WidgetDOM';
import { createErrorResponse, createSuccessResponse, createRequest, parseIncomingMessageJS, WidgetResponse, WidgetRequest } from './Message'

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {WidgetRequest} widgetRequest
 */
const createDispatchResponse = (eventName, widget, widgetRequest) => (err, data) => {
  const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
  if (! widgetWindow) {
    throw new Error('can not find widget window');
  }
  const widgetResponse = err ? createErrorResponse(widgetRequest, err) : createSuccessResponse(widgetRequest, data);

  postRobot.send(widgetWindow, eventName, widgetResponse.toJS());
};

export const dispatchIncomingMessage = (eventName, widgetMessage, widget, eventDispatcher ) => {
  //parse message
  const message = parseIncomingMessageJS(widgetMessage);

  if (message instanceof WidgetRequest) {
    const callback = createDispatchResponse(eventName, widget, message);
    eventDispatcher.emit(eventName, callback, widget, message);
    return;
  }

  if (message instanceof WidgetResponse) {
    eventDispatcher.emit(message.id, widget, message);
  }

};

/**
 * @param eventName
 * @param message
 * @param {EventSubscribersRegistry} eventSubscriberRegistry
 */
export const dispatchOutgoingMessage = (eventName, message, eventSubscriberRegistry) =>
{
  const widgetList = eventSubscriberRegistry.getSubscribers(eventName);
  const widgetWindowList = widgetList.map(widget => WidgetDOM.findWidgetWindow(widget, window.document));
  const widgetRequestList = widgetList.map(widget => createRequest(widget, message));

  while (widgetList.length) {
    const widgetWindow = widgetWindowList.pop();
    const widgetRequest = widgetRequestList.pop();

    if (widgetWindow) {
      postRobot.send(widgetWindow, eventName, widgetRequest.toJS());
    }
  }
};
