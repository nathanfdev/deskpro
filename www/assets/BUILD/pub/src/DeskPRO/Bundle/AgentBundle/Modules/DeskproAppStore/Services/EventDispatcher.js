import { EventEmitter } from 'eventemitter3';

export class EventDispatcher extends EventEmitter
{}

export const RequestEventDispatcher = new EventDispatcher();
export const ResponseEventDispatcher = new EventDispatcher();

