import Immutable from 'immutable';
import isPlainObject from 'lodash/lang/isPlainObject';

function verifyImmutable(...args) {
  args.forEach(val => {
    if (!Immutable.Iterable.isIterable(val)) {
      throw new TypeError('Expected an Immutable');
    }
  });
}

function verifyIsMapish(value) {
  if (!Immutable.Map.isMap(value) && !isPlainObject(value)) {
    throw new TypeError('Expected an Immutable.Map or Object');
  }
}

function verifyActionError(action) {
  if (Immutable.Map.isMap(action)) {
    if (action.get('error') !== true) {
      return;
    }
  } else {
    if (!action || !action.error || action.error !== true) {
      return;
    }
  }

  console.error('Error with action ' + action.get('type'), action.payload);
  throw action.payload;
}

/**
 * Set a value on the state.
 *
 * @param {String} statePropKey  The property to set on the state.
 * @param {any}    value         The value to set
 * @return {Function} Action handler function
 */
export function setValue(statePropKey, value) {
  return (state, payload, action) => {
    verifyActionError(action);
    verifyImmutable(state);
    return state.setIn(statePropKey.split('.'), value);
  };
}


/**
 * Set a value on the state.
 *
 * @param {String} statePropKey  The property to set on the state. If null, the value will be merged into the whole state.
 * @param {any}    value         The value to set
 * @param {Boolean} deep         Merge deep
 * @return {Function} Action handler function
 */
export function mergeValue(statePropKey, value, deep = false) {
  return (state, payload, action) => {
    verifyActionError(action);
    verifyImmutable(state);

    if (statePropKey) {
      if (deep) {
        return state.mergeDeepIn(statePropKey.split('.'), value);
      }
      return state.mergeIn(statePropKey.split('.'), value);
    }

    if (deep) {
      return state.mergeDeep(value);
    }
    return state.merge(value);
  };
}


/**
 * Set a value from the action payload.
 *
 * @param {String} statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {String} payloadPropKey  The property to read from the payload. Use '@' to mean same as statePropKey, or null to mean the payload itself is the value to set.
 * @param {any}    defaultValue    The value to set if the payload doesn't contain the requested property.
 * @return {Function} Action handler function
 */
export function setPayload(statePropKey, payloadPropKey = '@', defaultValue = null) {
  return (state, payload, action) => {
    verifyActionError(action);
    verifyImmutable(state);

    const usePayloadPropKey = payloadPropKey === '@' ? statePropKey : payloadPropKey;

    let value;
    if (usePayloadPropKey) {
      if (payload) {
        verifyIsMapish(payload);
        value = Immutable.fromJS(payload).getIn(usePayloadPropKey.split('.'), defaultValue);
      } else {
        value = defaultValue;
      }
    } else {
      value = payload || defaultValue;
    }

    if (statePropKey) {
      return state.setIn(statePropKey.split('.'), value);
    }

    verifyIsMapish(value);
    return Immutable.fromJS(value);
  };
}

/**
 * Like setPayload, except this sets the entire payload. This is the same as passing null as the payloadPropKey to setPayload,
 * it's just a little more intuitive.
 *
 * @param {String} statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {any}    defaultValue    The value to set if the payload doesn't contain the requested property.
 * @return {Function} Action handler function
 */
export function setFullPayload(statePropKey, defaultValue = null) {
  return setPayload(statePropKey, null, defaultValue);
}


/**
 * Merge a value from the action payload.
 *
 * @param {String}   statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {String}   payloadPropKey  The property to read from the payload. Use '@' to mean same as statePropKey, or null to mean the payload itself is the value to set.
 * @param {any}      defaultValue    The value to set if the payload doesn't contain the requested property.
 * @param {Boolean}  deep            Merge deep
 * @return {Function} Action handler function
 */
export function mergePayload(statePropKey = null, payloadPropKey = '@', defaultValue = {}, deep = false) {
  return (state, payload, action) => {
    verifyActionError(action);
    verifyImmutable(state);

    const usePayloadPropKey = payloadPropKey === '@' ? statePropKey : payloadPropKey;

    let value;
    if (usePayloadPropKey) {
      if (payload) {
        verifyIsMapish(payload);
        value = Immutable.fromJS(payload).getIn(usePayloadPropKey.split('.'), defaultValue);
      } else {
        value = defaultValue;
      }
    } else {
      value = payload || defaultValue;
    }

    verifyIsMapish(value);

    if (statePropKey) {
      if (deep) {
        return state.mergeDeepIn(statePropKey.split('.'), value);
      }

      return state.mergeIn(statePropKey.split('.'), value);
    }

    if (deep) {
      return state.mergeDeep(value);
    }

    return state.merge(value);
  };
}


/**
 * Like mergePayload, except this sets the entire payload. This is the same as passing null as the payloadPropKey to setPayload,
 * it's just a little more intuitive.
 *
 * @param {String}   statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {any}      defaultValue    The value to set if the payload doesn't contain the requested property.
 * @param {Boolean}  deep            Merge deep
 * @return {Function} Action handler function
 */
export function mergeFullPayload(statePropKey = null, defaultValue = {}, deep = false) {
  return mergePayload(statePropKey, null, defaultValue, deep);
}


/**
 * Handle an async action.
 *
 * @param {Function} handlers           Functions to handle each sequence in an async action
 * @param {Function} handlers.start     Called as soon as the action is dispatched
 * @param {Function} handlers.success   Called once the promise resolved
 * @param {Function} handlers.error     Called if the promise is rejected
 * @param {Function} handlers.done      Called when the promise is finished, after both success and error
 * @return {Function} A handler function
 */
export function async({ start, success, error, done }) {
  return (state, payload, action) => {
    const seq = action && action.meta && action.meta.sequence ? action.meta.sequence : null;

    switch (seq) {
      case 'start':
        if (start) {
          return start(state, payload, action);
        }
        break;
      case 'success':
        if (success) {
          verifyActionError(action);
          return success(state, payload, action);
        }
        break;
      case 'error':
        if (error) {
          return error(state, payload, action);
        }
        break;
      case 'done':
        if (done) {
          return done(state, payload, action);
        }
        break;
      default:
        return state;
    }

    return state;
  };
}

function _resolveProps(rawProps, state, payload, action) {
  let props = rawProps;
  if (typeof props === 'function') {
    props = props(state, payload, action);
  }

  // Single param may be used to represent just the loading state
  if (!isPlainObject(props)) {
    props = { loading: props };
  }

  let key;
  for (key in props) {
    if (!props.hasOwnProperty(key)) {
      continue;
    }
    if (typeof props[key] === 'function') {
      props[key] = props[key](state, payload, action);
    }
    if (props[key] && props[key].indexOf('.') !== -1) {
      props[key] = props[key].split('.');
    }
  }

  return props;
}

/**
 * Handle setting a loading indicator on state for a async action.
 *
 * @param {Object} props          The properties to set for each sequence. It may be a string, array or a function. If a function, it will be passed the (state, payload, action) and must return a string or array.
 * @param {Object} props.loading  Set on start and unset on done.
 * @param {Object} props.success  Set on success
 * @param {Object} props.error    Set on error
 * @return {Function} Action handler function
 */
export function asyncIndicator(props) {
  return (state, payload, action) => {
    verifyImmutable(state);

    const seq = action && action.meta && action.meta.sequence ? action.meta.sequence : null;
    const useProps = _resolveProps(props, state, payload, action);

    let newState = state;

    switch (seq) {
      case 'start':
        if (useProps.loading) {
          newState = newState.setIn(useProps.loading, true);
        }
        if (useProps.success) {
          newState = newState.setIn(useProps.success, false);
        }
        if (useProps.isError) {
          newState = newState.setIn(useProps.isError, false);
        }
        if (useProps.errorCode) {
          newState = newState.setIn(useProps.errorCode, null);
        }
        break;
      case 'success':
        if (useProps.success) {
          newState = newState.setIn(useProps.success, true);
        }
        if (useProps.isError) {
          newState = newState.setIn(useProps.isError, false);
        }
        if (useProps.errorCode) {
          newState = newState.setIn(useProps.errorCode, null);
        }
        break;
      case 'error':
        if (useProps.success) {
          newState = newState.setIn(useProps.success, false);
        }
        if (useProps.isError) {
          newState = newState.setIn(useProps.isError, true);
        }
        if (useProps.errorCode && payload.response) {
          newState = newState.setIn(useProps.errorCode, payload.response.xhr.status);
        }
        break;
      case 'done':
        if (useProps.loading) {
          newState = newState.setIn(useProps.loading, false);
        }
        break;
      default:
        return state;
    }

    return newState;
  };
}

/**
 * Compose several action handlers together. The value returned will the last value.
 *
 * @param {Function[]} funcs...  Functions you want to compose
 * @return {Function} Composed functions
 */
export function composeHandlers(...funcs) {
  return (state, payload, action) => funcs.reduceRight((composed, fn) => fn(composed, payload, action), state);
}
