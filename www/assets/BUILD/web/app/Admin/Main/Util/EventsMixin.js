// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(() =>
  function(baseObj) {
    baseObj._dp_listeners = {};

    baseObj.notifyListeners = function(event_name, args) {
      if (args == null) { args = []; }
      let notified = 0;
      if (!baseObj._dp_listeners[event_name]) {
        return 0;
      }

      for (let listener of Array.from(baseObj._dp_listeners[event_name])) {
        listener.apply(listener, args);
        notified += 1;
      }

      return notified;
    };

    baseObj.addListener = function(event_name, listener) {
      if (baseObj.hasListener(listener, event_name)) {
        return false;
      }

      if (!baseObj._dp_listeners[event_name]) {
        baseObj._dp_listeners[event_name] = [];
      }

      baseObj._dp_listeners[event_name].push(listener);
      return true;
    };

    baseObj.hasListener = function(event_name, listener) {
      if (!baseObj._dp_listeners[event_name]) {
        return false;
      }

      return baseObj._dp_listeners[event_name].indexOf(listener) !== -1;
    };

    return baseObj.removeListener = function(event_name, listener) {
      if (!baseObj._dp_listeners[event_name]) {
        return false;
      }

      const idx = baseObj._dp_listeners[event_name].indexOf(listener);
      if (idx === -1) {
        return false;
      }

      baseObj._dp_listeners[event_name] = baseObj._dp_listeners[event_name].splice(idx, 1);

      if (!baseObj._dp_listeners[event_name].length) {
        delete baseObj._dp_listeners[event_name];
      }

      return true;
    };
  }
);