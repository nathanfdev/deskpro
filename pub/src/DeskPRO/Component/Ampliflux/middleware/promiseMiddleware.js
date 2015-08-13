import { isDSA } from '../actions/actionUtils';
import uniqueId from 'lodash/utility/uniqueId';

/**
 * Given an action, fetch the promise from it if it exists.
 */
function getPromise(action) {
  if (!isDSA(action)) {
    return null;
  }

  // Already been dispatched through the system
  if (typeof action.sequence !== 'undefined') {
    return null;
  }

  if (typeof action.payload.then === "function") {
    return action.payload;
  } else if (action.payload.promise) {
    if (typeof action.payload.promise.then !== "function") {
      throw new Error("action.payload.promise is not a promise");
    }

    return action.payload.promise;
  } else {
    return null;
  }
}

function getSeqBaseType(action) {
  if (action.parentType) {
    return action.parentType;
  } else {
    return action.type;
  }
}

/**
 * PromiseMiddleware handles promises and dispatches before/after states.
 *
 * The action payload can either be a promise itself, or an object with a `promise`
 * key. The reason you might want to return an object is because you can pass
 * other data in the payload which will be dispatched in the 'before' action.
 *
 * `dispatch` will return the promise.
 */
export default function promiseMiddleware({ dispatch }) {
  return next => action => {
    const promise = getPromise(action);

    if (!promise) {
      // no promise, this middleware does nothing
      return next(action);
    }

    const baseType = getSeqBaseType(action);

    const sequenceId = uniqueId();
    dispatch({
      ...action,
      type: baseType + ".START",
      parentType: null,
      payload: action.payload || {},
      sequence: {
        type: "start",
        id: sequenceId,
      }
    });

    const nextAction = {
      ...action,
      parentType: null,
      sequence: {
        type: "next",
        id: sequenceId,
      }
    }

    return promise
      .then(result => {
        const a = {
          ...nextAction,
          payload: result
        };
        dispatch({ ...a, type: baseType});
        dispatch({ ...a, type: baseType + ".DONE"});
      })
      .catch(error => {
        const a = {
          ...nextAction,
          payload: error,
          error: true
        };
        dispatch({ ...a, type: baseType + ".ERROR"});
        dispatch({ ...a, type: baseType + ".DONE"});
      });
  }
}
