import { IncomingEventDispatcher, OutgoingEventDispatcher } from './EventDispatchers';
import { dispatchIncomingMessage, createDispatchRequest } from './MessageDispatcher';

import { registerListeners as registerIncomingRequestListeners  } from './IncomingRequestHandlers';
import { registerListeners as registerOutgoingRequestListeners  } from './OutgoingRequestHandlers';

export const dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget) => dispatchIncomingMessage(eventName, widgetMessage, widget, IncomingEventDispatcher);
export const dispatchOutgoingWidgetMessage = (eventName, widgetMessage) => dispatchOutgoingMessage(eventName, widgetMessage, OutgoingEventDispatcher);

/**
 * @param {AppServices} appServices
 */
export const registerIncomingWidgetRequestListeners = appServices => registerIncomingRequestListeners(IncomingEventDispatcher, appServices);
/**
 * @param messageBroker
 * @param {AppServices} appServices
 */
export const registerOutgoingWidgetRequestListeners = (appServices, messageBroker) => registerOutgoingRequestListeners(OutgoingEventDispatcher, appServices, messageBroker);

/**
 * @param {String} eventName
 * @param {Widget} widget
 * @param {AppServices} services
 */
export const addWidgetEventListener = (eventName, widget, services) => {
  const send = createDispatchRequest(eventName, widget);
  OutgoingEventDispatcher.addListener(eventName, (message, handler) => handler(send, widget, message, services));
};

