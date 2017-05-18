import postRobot from 'post-robot/src';
import { default as serializeError } from 'serialize-error';

import * as WidgetDOM from '../WidgetDOM';

/**
 * @param eventName
 * @param {Widget} widget
 * @param {Window} widgetWindow
 * @param {WidgetMessage} widgetRequestMessage
 * @param err
 * @param data
 */
const callback = (eventName, widget, widgetWindow, widgetRequestMessage, err, data) => {
  let message;

  if (err) {
    message = {
      status: 'error',
      body: err instanceof Error ? JSON.stringify(serializeError(err)) : JSON.stringify(err)
    }
  } else {
    message = { status: 'success', body: JSON.stringify(data) };
  }

  postRobot.send(widgetWindow, eventName, { id: widgetRequestMessage.id, ...message });
};

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {WidgetMessage} widgetRequestMessage
 */
export const createCallback = (eventName, widget, widgetRequestMessage) => (err, data) => {
  const widgetWindow = WidgetDOM.findWidgetWindow(widget, window.document);
  if (! widgetWindow) {
    throw new Error('can not find widget window');
  }

  callback(eventName, widget, widgetWindow, widgetRequestMessage, err, data)
};

/**
 * @param {EventEmitter} eventDispatcher
 */
export const createDispatchRequest = eventDispatcher => (eventName, widget, widgetMessage) => {
  const callback = createCallback(eventName, widget, widgetMessage);
  eventDispatcher.emit(eventName, callback, widget, widgetMessage);
};
