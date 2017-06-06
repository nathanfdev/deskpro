import { RequestEventDispatcher } from '../Services/EventDispatcher';
import { dispatchIncomingMessage, dispatchOutgoingMessage } from './MessageDispatcher';

export * as WidgetRequestHandlers from './RequestHandlers';

export { registerListeners as registerWidgetRequestListeners  } from './RequestHandlers';
export { EventSubscribersRegistry } from './EventSubscribersRegistry';

export const dispatchIncomingWidgetMessage = (eventName, widgetMessage, widget) => dispatchIncomingMessage(eventName, widgetMessage, widget, RequestEventDispatcher);
export const dispatchOutgoingWidgetMessage = (eventName, widgetMessage) => dispatchOutgoingMessage(eventName, widgetMessage, null);
