import { EventEmitter } from 'eventemitter3/index';

export default class EventDispatcher extends EventEmitter {
  /**
   * @param {String} eventName
   * @param {function} listener
   * @return {function} a function that can be used to remove the listener
   */
  addRemovableListener = (eventName, listener) =>  {
    this.addListener(eventName, listener);
    return this.removeListener.bind(this, eventName, listener);
  }
}
