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

    // the action payload is itself some action, we will dispatch that instead
    // - This is common when an async action resolves and we want to dispatch something
    // with the value.
    // - If we didnt do this, we'd need to return a function to get dispatch, so handling
    // this case is a shortcut
    } else if (isDSA(action.payload)) {
      return dispatch({
        ...action.payload,
        parentType: action.type
      });
    } else {
      return next(action);
    }
  }
}
