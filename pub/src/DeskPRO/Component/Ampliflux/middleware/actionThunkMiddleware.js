import { isDSA } from '../actions/actionUtils';
import Immutable from "immutable";

/**
 * Similar to redux-thunk except it also works if a function is returned as part of a payload.
 */
export default function actionThunkMiddleware({ dispatch, getState }) {
  return next => action => {
    // simple function -- similar to redux-thunk
    if (typeof action === 'function') {
      return action(dispatch, getState);
    }

    // Handle Immutable.Map action
    if (Immutable.Map.isMap(action)) {
      // 1) Payload is a function: Call it and dispatch the result
      if (isDSA(action) && action.has('payload') && action.get('payload') === 'function') {
        return dispatch(action.get('payload')(dispatch, getState, action));

      // 2) The payload is a DSA action, re-dispatch the new action
      } else if (action.has('payload') && isDSA(action.get('payload'))) {
        return dispatch(action.get('payload'))
      }

    // Handle plain object action
    // Same as above. We handle both cases verbosely like this because
    // this middleware is called so many times, we want to be as efficient as possible.
    } else {
      if (isDSA(action) && action.payload && action.payload === 'function') {
        return dispatch(action.payload(dispatch, getState, action));
      } else if (action.payload && isDSA(action.payload)) {
        return dispatch(action.payload)
      }
    }

    return next(action);
  }
}
