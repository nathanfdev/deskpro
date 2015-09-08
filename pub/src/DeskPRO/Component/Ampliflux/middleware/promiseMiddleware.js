import { isDSA, getActionType } from '../actions/actionUtils';
import uniqueId from 'lodash/utility/uniqueId';
import Immutable from "immutable";

/**
 * Given an action, fetch the promise from it if it exists.
 */
function getPromise(action) {
  let payload;
  if (Immutable.Map.isMap(action)) {
      if (!action.get('payload') || action.getIn(['meta', 'sequenceType']) === 'promise') {
        return null;
      }

      payload = action.get('payload');

      if (typeof payload.then === "function") {
        return payload;
      } else if (typeof payload.has === 'function' && payload.has('promise')) {
        return payload.get('promise');
      } else if (typeof payload.promise !== 'undefined') {
        return payload.promise;
      } else {
        return null;
      }
  } else {
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
    if (!isDSA(action)) {
      return null;
    }

    const promise = getPromise(action);

    if (!promise) {
      // no promise, this middleware does nothing
      return next(action);
    }

    if (!Immutable.Map.isMap(action)) {
      action = Immutable.fromJS(action);
    }

    const sequenceId = uniqueId();

    const createSeqAction = (sequence, payload, isError = false) => action.withMutations(v => {
      v.setIn(['meta', 'sequenceId'], sequenceId);
      v.setIn(['meta', 'sequence'], sequence);
      v.setIn(['meta', 'sequenceType'], 'promise');
      v.set('payload', payload);
      if (isError) {
        v.set('error', true);
      }
    });

    dispatch(createSeqAction('start', {
      promise: promise,
      originalPayload: action.get('payload')
    }));

    return promise
      .then(result => {
        dispatch(createSeqAction('success', result));
        dispatch(createSeqAction('done', result));
      })
      .catch(error => {
        dispatch(createSeqAction('success', result, true));
        dispatch(createSeqAction('done', result, true));
      });
  }
}
