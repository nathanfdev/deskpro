import { isDSA } from '../actions/actionUtils';

/**
 * If an action has a payload that is itself a DSA action,
 * then discard the action and dispatch the child action.
 */
export default function redispatchDsaPayload({ dispatch, getState }) {
  return next => action => {
    if (isDSA(action)) {
      let payload = action && action.payload ? action.payload : null;
      if (payload && isDSA(payload)) {
        return dispatch(action.payload);
      }
    }

    return next(action);
  }
}
