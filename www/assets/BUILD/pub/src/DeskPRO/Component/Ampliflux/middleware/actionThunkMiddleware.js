import { isDSA } from '../actions/actionUtils';

function copyActionNewPayload(action, payload) {
  return {
    ...action,
    payload
  };
}

/**
 * Similar to redux-thunk except it also works if a function is returned as part of a payload.
 * @return {Function} middleware
 */
export function actionThunkMiddleware({ dispatch, getState }) {
  return next => action => {
    let actionFn = null;

    // simple function -- similar to redux-thunk
    if (typeof action === 'function') {
      actionFn = action;
    } else if (isDSA(action)) {
      if (action.payload && typeof action.payload === 'function') {
        actionFn = action.payload;
      }
    }

    if (actionFn) {
      const result = actionFn(dispatch, getState, action);
      // undefined means the function will use dispatch itself
      if (typeof result === 'undefined') {
        return result;
      }

      // otherwise, pass the value thru
      if (isDSA(action) && !isDSA(result)) {
        return dispatch(copyActionNewPayload(action, result));
      }

      return next(result);
    }

    return next(action);
  };
}
