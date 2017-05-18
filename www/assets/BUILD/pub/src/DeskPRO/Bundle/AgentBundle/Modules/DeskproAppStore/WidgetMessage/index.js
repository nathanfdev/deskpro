import { RequestEventDispatcher } from '../Services/EventDispatcher';
import { createDispatchRequest } from './MessageDispatcher';

export * as WidgetMessageDispatcher from './MessageDispatcher';
export * as WidgetRequestHandlers from './RequestHandlers';
export { registerListeners as registerWidgetRequestListeners  } from './RequestHandlers';
export { WidgetMessage } from './WidgetMessage';

export const dispatchWidgetRequestEvent = createDispatchRequest(RequestEventDispatcher);
