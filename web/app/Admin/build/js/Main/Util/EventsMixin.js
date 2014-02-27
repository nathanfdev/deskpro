(function() {
  define(function() {
    return function(baseObj) {
      baseObj._dp_listeners = {};
      baseObj.notifyListeners = function(event_name, args) {
        var listener, notified, _i, _len, _ref;
        if (args == null) {
          args = [];
        }
        notified = 0;
        if (!baseObj._dp_listeners[event_name]) {
          return 0;
        }
        _ref = baseObj._dp_listeners[event_name];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          listener = _ref[_i];
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
        var idx;
        if (!baseObj._dp_listeners[event_name]) {
          return false;
        }
        idx = baseObj._dp_listeners[event_name].indexOf(listener);
        if (idx === -1) {
          return false;
        }
        baseObj._dp_listeners[event_name] = baseObj._dp_listeners[event_name].splice(idx, 1);
        if (!baseObj._dp_listeners[event_name].length) {
          delete baseObj._dp_listeners[event_name];
        }
        return true;
      };
    };
  });

}).call(this);

//# sourceMappingURL=EventsMixin.js.map
