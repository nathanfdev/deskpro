import { EventEmitter } from 'eventemitter3';

export class EventDispatcher extends EventEmitter
{}

export const IncomingEventDispatcher = new EventDispatcher();
export const OutgoingEventDispatcher = new EventDispatcher();
