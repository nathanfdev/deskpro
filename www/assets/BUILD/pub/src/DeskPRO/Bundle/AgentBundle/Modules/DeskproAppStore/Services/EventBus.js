/**
 * A very simple implementation of an event to decouple communication between services
 */
class EventBus
{
    constructor()
    {
      this.listeners = {};
    }

  /**
   * @param {String} type
   * @param {Function} listener
   */
    addEventListener = (type, listener) =>
    {
      if(typeof this.listeners[type] != 'undefined') {
        this.listeners[type].push({ callback: listener });
      } else {
        this.listeners[type] = [{ callback: listener }];
      }
    };

  /**
   * Dispatches an event and returns the number of invoked listeners
   *
   * @param {String} type
   * @param {Array} args
   * @return {number}
   */
    dispatch = (type, ...args) =>
    {
      let dispatched = 0;

      if(typeof this.listeners[type] != 'undefined') {
        for (let listenerDef of this.listeners[type]) {
          dispatched++;
          listenerDef.callback.apply(this, args);
        }
      }

      return dispatched;
    };
}

export default EventBus;
