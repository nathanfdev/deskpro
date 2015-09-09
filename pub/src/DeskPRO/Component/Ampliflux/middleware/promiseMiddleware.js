import { isDSA, getActionType } from '../actions/actionUtils';
import uniqueId from 'lodash/utility/uniqueId';

/**
 * Given an action, fetch the promise from it if it exists.
 */
function getPromise(action) {
  let payload;
  // The whole action is itself a promise
  if (typeof action.then === 'function') {
    return action;
  }

  if (typeof action.payload === 'undefined' || !action.payload || (action.meta && action.meta.sequenceType && action.meta.sequenceType === 'promise')) {
    return null;
  }

  payload = action.payload;

  if (typeof payload.then === "function") {
    return payload;
  } else if (typeof payload.promise !== 'undefined') {
    return payload.promise;
  } else {
    return null;
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
    if (!action) {
      return next(action);
    }

    const promise = getPromise(action);

    if (!promise) {
      // no promise, this middleware does nothing
      return next(action);
    }

    if (!isDSA(action)) {
      // It's just a lonely action so we will just wait on it resolving
      promise.then(result => {
        dispatch(result);
      }).catch(result => {
        dispatch(result);
      });
    } else {
      const sequenceId = uniqueId();

      const createSeqAction = (sequence, payload, isError = false) => {
        let copyAction = {
          ...action,
          payload: payload,
          error: isError === true,
          meta: {
            ...action.meta,
            sequenceId:      sequenceId,
            sequence:        sequence,
            sequenceType:    'promise',
            previousPayload: action.payload
          }
        };
        return copyAction;
      }

      dispatch(createSeqAction('start', action.payload));

      promise
        .then(result => {
          dispatch(createSeqAction('success', result));
          dispatch(createSeqAction('done', result));
        })
        .catch(error => {
          dispatch(createSeqAction('error', result, true));
          dispatch(createSeqAction('done', result, true));
        });

      return promise;
    }
  }
}
