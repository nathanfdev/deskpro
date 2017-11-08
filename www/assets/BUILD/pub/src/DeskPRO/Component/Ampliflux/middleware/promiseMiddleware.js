import uniqueId from 'lodash/uniqueId';
import { isDSA } from '../actions/actionUtils';

/**
 * Given an action, fetch the promise from it if it exists.
 *
 * @param {any} action The action provided
 * @return {Promise|null} A promise if found, or null;
 */
function getPromise(action) {
  // The whole action is itself a promise
  if (typeof action.then === 'function') {
    return action;
  }

  if (typeof action.payload === 'undefined'
    || !action.payload
    || (action.meta && action.meta.sequenceType && action.meta.sequenceType === 'promise')) {
    return null;
  }

  const payload = action.payload;

  if (typeof payload.then === 'function') {
    return payload;
  } else if (typeof payload.promise !== 'undefined') {
    return payload.promise;
  }

  return null;
}

/**
 * PromiseMiddleware handles promises and dispatches before/after actions.
 *
 * The action payload can either be a promise itself, or an object with a `promise`
 * key. The reason you might want to return an object is because you can pass
 * other data in the payload which will be dispatched in the 'before' action.
 *
 * `dispatch` will return the promise.
 *
 * @return {Function} middleware
 */
export function promiseMiddleware({ dispatch }) {
  return next => (action) => {
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
      promise.then((result) => {
        if (result) {
          dispatch(result);
        }
      }).catch((result) => {
        if (result) {
          dispatch(result);
        }
      });
    } else {
      const sequenceId = uniqueId();
      const actionMeta = action.meta;

      const createSeqAction = (sequence, payload, isError = false) => ({
        ...action,
        payload,
        error: isError === true,
        meta:  {
          ...actionMeta,
          sequenceId,
          sequence,
          sequenceType:    'promise',
          previousPayload: action.payload
        }
      });

      dispatch(createSeqAction('start', action.payload));

      promise
        .then((result) => {
          dispatch(createSeqAction('success', result));
          dispatch(createSeqAction('done', result));
        })
        .catch((error) => {
          dispatch(createSeqAction('error', error, true));
          dispatch(createSeqAction('done', error, true));
        });

      return promise;
    }
  };
}
