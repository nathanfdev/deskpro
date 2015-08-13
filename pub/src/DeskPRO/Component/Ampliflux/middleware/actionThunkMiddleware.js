import { isDSA } from '../actions/actionUtils';

/**
 * Similar to redux-thunk except it also works if a function is returned as part of a payload.
 */
export default function actionThunkMiddleware({ dispatch, getState }) {
  return next => action => {
    // simple function -- similar to redux-thunk
    if (typeof action === 'function') {
      return action(dispatch, getState);

    // if it's already a payload, then we will re-dispatch it
    } else if (isDSA(action) && typeof action.payload === 'function') {
      return dispatch({
        type: "ACTION_REDISPATCH",
        parentType: action.type,
        payload: action.payload(dispatch, getState, action)
      });
    } else {
      return next(action);
    }
  }
}
