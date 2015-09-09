import Immutable from "immutable";
import isPlainObject from 'lodash/lang/isPlainObject';

function verifyImmutable(...args) {
  args.forEach(v => {
    if (!Immutable.Iterable.isIterable(v)) {
      throw new TypeError('Expected an Immutable');
    }
  });
}

function verifyIsMapish(value) {
  if (!Immutable.Map.isMap(value) && !isPlainObject(value)) {
    throw new TypeError('Expected an Immutable.Map or Object');
  }
}

function isScalar(val) {
  return (/boolean|number|string/).test(typeof val);
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

    action = Immutable.fromJS(action);
  }

  console.error("Error with action " + action.get('type'), action.payload);
  throw action.payload;
}

/**
 * Set a value on the state.
 *
 * @param {String} statePropKey  The property to set on the state.
 * @param {any}    value         The value to set
 * @return {Immutable.Map}
 */
export const setValue = (statePropKey, value) => (state, payload, action) => {
  verifyActionError(action);
  verifyImmutable(state);
  return state.setIn(statePropKey.split('.'), value);
};


/**
 * Set a value on the state.
 *
 * @param {String} statePropKey  The property to set on the state. If null, the value will be merged into the whole state.
 * @param {any}    value         The value to set
 * @param {Boolean} deep         Merge deep
 * @return {Immutable.Map}
 */
export const mergeValue = (statePropKey, value, deep = false) => (state, payload, action) => {
  verifyActionError(action);
  verifyImmutable(state);

  if (statePropKey) {
    if (deep) {
      return state.mergeDeepIn(statePropKey.split('.'), value);
    } else {
      return state.mergeIn(statePropKey.split('.'), value);
    }
  } else {
    if (deep) {
      return state.mergeDeep(value);
    } else {
      return state.merge(value);
    }
  }
};


/**
 * Set a value from the action payload.
 *
 * @param {String} statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {String} payloadPropKey  The property to read from the payload. Use '@' to mean same as statePropKey, or null to mean the payload itself is the value to set.
 * @param {any}    defaultValue    The value to set if the payload doesn't contain the requested property.
 * @return {Immutable.Map}
 */
export const setPayload = (statePropKey, payloadPropKey = '@', defaultValue = null) => (state, payload, action) => {
  verifyActionError(action);
  verifyImmutable(state);

  if (payloadPropKey === '@') {
    payloadPropKey = statePropKey;
  }

  let value;
  if (payloadPropKey) {
    if (payload) {
      verifyIsMapish(payload);
      value = Immutable.fromJS(payload).getIn(payloadPropKey.split('.'), defaultValue);
    } else {
      value = defaultValue;
    }
  } else {
    value = payload || defaultValue;
  }

  if (statePropKey) {
    return state.setIn(statePropKey.split('.'), value);
  } else {
    verifyIsMapish(value);
    return Immutable.fromJS(value);
  }
}

/**
 * Like setPayload, except this sets the entire payload. This is the same as passing null as the payloadPropKey to setPayload,
 * it's just a little more intuitive.
 *
 * @param {String} statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {any}    defaultValue    The value to set if the payload doesn't contain the requested property.
 * @return {Immutable.Map}
 */
export const setFullPayload = (statePropKey, defaultValue = null) => {
  return setPayload(statePropKey, null, defaultValue);
}


/**
 * Merge a value from the action payload.
 *
 * @param {String}   statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {String}   payloadPropKey  The property to read from the payload. Use '@' to mean same as statePropKey, or null to mean the payload itself is the value to set.
 * @param {any}      defaultValue    The value to set if the payload doesn't contain the requested property.
 * @param {Boolean}  deep            Merge deep
 * @return {Immutable.Map}
 */
export const mergePayload = (statePropKey = null, payloadPropKey = '@', defaultValue = {}, deep = false) => (state, payload, action) => {
  verifyActionError(action);
  verifyImmutable(state);

  if (payloadPropKey === '@') {
    payloadPropKey = statePropKey;
  }

  let value;
  if (payloadPropKey) {
    if (payload) {
      verifyIsMapish(payload);
      value = Immutable.fromJS(payload).getIn(payloadPropKey.split('.'), defaultValue);
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
    } else {
      return state.mergeIn(statePropKey.split('.'), value);
    }
  } else {
    if (deep) {
      return state.mergeDeep(value);
    } else {
      return state.merge(value);
    }
  }
}


/**
 * Like mergePayload, except this sets the entire payload. This is the same as passing null as the payloadPropKey to setPayload,
 * it's just a little more intuitive.
 *
 * @param {String}   statePropKey    The property to set on the state. If null, the whole state will be set.
 * @param {any}      defaultValue    The value to set if the payload doesn't contain the requested property.
 * @param {Boolean}  deep            Merge deep
 * @return {Immutable.Map}
 */
export const mergeFullPayload = (statePropKey = null, defaultValue = {}, deep = false) => {
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
 * @return {Function}
 */
export const async = ({ start, success, error, done }) => (state, payload, action) => {
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
  }
  return state;
}


/**
 * Handle setting a loading indicator on state for a async action.
 *
 * @param {Object} props          The properties to set for each sequence. It may be a string, array or a function. If a function, it will be passed the (state, payload, action) and must return a string or array.
 * @param {Object} props.loading  Set on start and unset on done.
 * @param {Object} props.success  Set on success
 * @param {Object} props.error    Set on error
 * @return {Immutable.Map}
 */
export const asyncIndicator = (props) => (state, payload, action) => {
  verifyImmutable(state);

  const seq = Immutable.Map.isMap(action) ? action.getIn(['meta', 'sequence']) : (action && action.meta && action.meta.sequence ? action.meta.sequence : null);

  if (typeof props === 'function') {
    props = props(state, payload, action);
  }

  // Single param may be used to represent just the loading state
  if (!isPlainObject(props)) {
    props = { loading: props };
  }

  for (let k in props) {
    if (typeof props[k] === 'function') {
      props[k] = props[k](state, payload, action);
    }
    if (props[k] && props[k].indexOf('.') !== -1) {
      props[k] = props[k].split('.');
    }
  }

  switch (seq) {
    case 'start':
      if (props.loading) state = state.setIn(props.loading, true);
      if (props.success) state = state.setIn(props.success, false);
      if (props.error)   state = state.setIn(props.error,   false);
      break;
    case 'success':
      if (props.success) state = state.setIn(props.success, true);
      if (props.error)   state = state.setIn(props.error,   false);
      break;
    case 'error':
      if (props.error)   state = state.setIn(props.error,   true);
      if (props.success) state = state.setIn(props.success, false);
      break;
    case 'done':
      if (props.loading) state = state.setIn(props.loading, false);
      break;
  }

  return state;
}

/**
 * Compose several action handlers together. The value returned will the last value.
 *
 * @param {Function[]} ...funcs  Functions you want to compose
 * @return {Function}
 */
export const composeHandlers = (...funcs) => (state, payload, action) => funcs.reduceRight((composed, f) => f(composed, payload, action), state);
