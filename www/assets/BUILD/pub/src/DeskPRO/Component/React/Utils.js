import map from 'lodash/map';
import zipObject from 'lodash/zipObject';
import uniqueId from 'lodash/uniqueId';

export function createActions(actionObj) {
  const baseId = uniqueId();

  return zipObject(map(actionObj, (actionCreator, key) => {
    const actionId   = `${baseId}-${key}`;
    const asyncTypes = ['BEGIN', 'OK', 'FAIL'].map(state => `${actionId}-${state}`);

    const method = (...args) => {
      const result = actionCreator(...args);

      if (result instanceof Promise) {
        // Promise (async)
        return {
          types:   asyncTypes,
          promise: result,
        };
      } else if (typeof result === 'function') {
        // Function (async)
        return (...subArgs) => ({
          type: actionId,
          ...(result(...subArgs) || {})
        });
      }
        // Object (sync)
      return {
        type: actionId,
        ...(result || {})
      };
    };

    if (actionCreator._async === true) {
      const [begin, success, failure] = asyncTypes;
      method._id = {
        begin,
        success,
        failure
      };
    } else {
      method._id = actionId;
    }

    return [key, method];
  }));
}

export function createStore(initialState, handlers) {
  return (state = initialState, action = {}) => {
    if (handlers[action.type]) {
      return handlers[action.type](state, action);
    }
    return state;
  };
}

export function promiseMiddleware() {
  return next => (action) => {
    const { promise, types, ...rest } = action;
    if (!promise) {
      return next(action);
    }

    const [BEGIN, OK, FAIL] = types;
    next({ ...rest, type: BEGIN });
    return promise.then(
      result => next({ ...rest, result, type: OK }),
      error => next({ ...rest, error, type: FAIL })
    );
  };
}

export function asyncAction() {
  return (target, name, descriptor) => {
    descriptor.value._async = true;
    return descriptor;
  };
}
