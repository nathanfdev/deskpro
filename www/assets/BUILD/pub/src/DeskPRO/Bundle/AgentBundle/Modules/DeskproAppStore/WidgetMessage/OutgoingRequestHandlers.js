import { events } from './Events';

/**
 * @param {function} send
 * @param {Widget} widget
 * @param {*} message
 * @param {AppServices} services
 * @constructor
 */
export const EVENT_TICKET_REPLY = (send, widget, message, services) => {
  const { ticket_id, meta } = message;
  const { api_data, hasBilling, hasTimeLog } = meta;
  send({ api_data, hasBilling, hasTimeLog });
};

export const handlers = {

  // TICKET EVENTS

  EVENT_TICKET_REPLY,

};

/**
 * @param {EventDispatcher} eventDispatcher
 * @param {AppServices} appServices
 * @param {DeskPRO.MessageBroker} messageBroker
 */
export const registerListeners = (eventDispatcher, appServices, messageBroker) =>
{
  messageBroker.addMessageListener(
    events.EVENT_TICKET_REPLY,
    (message) => eventDispatcher.emit(events.EVENT_TICKET_REPLY, message, EVENT_TICKET_REPLY)
  );
};
