define ->
  return (baseObj) ->
    baseObj._dp_listeners = {}

    baseObj.notifyListeners = (event_name, args = []) ->
      notified = 0
      if not baseObj._dp_listeners[event_name]
        return 0

      for listener in baseObj._dp_listeners[event_name]
        listener.apply(listener, args)
        notified += 1

      return notified

    baseObj.addListener = (event_name, listener) ->
      if baseObj.hasListener(listener, event_name)
        return false

      if not baseObj._dp_listeners[event_name]
        baseObj._dp_listeners[event_name] = []

      baseObj._dp_listeners[event_name].push(listener)
      return true

    baseObj.hasListener = (event_name, listener) ->
      if not baseObj._dp_listeners[event_name]
        return false

      return baseObj._dp_listeners[event_name].indexOf(listener) != -1

    baseObj.removeListener = (event_name, listener) ->
      if not baseObj._dp_listeners[event_name]
        return false

      idx = baseObj._dp_listeners[event_name].indexOf(listener)
      if idx == -1
        return false

      baseObj._dp_listeners[event_name] = baseObj._dp_listeners[event_name].splice(idx, 1)

      if not baseObj._dp_listeners[event_name].length
        delete baseObj._dp_listeners[event_name]

      return true