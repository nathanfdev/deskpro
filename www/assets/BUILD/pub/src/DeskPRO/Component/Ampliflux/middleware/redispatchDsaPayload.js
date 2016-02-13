import { isDSA } from '../actions/actionUtils';

/**
 * If an action has a payload that is itself a DSA action,
 * then discard the action and dispatch the child action.
 *
 * @return {Function} middelware
 */
export function redispatchDsaPayload({ dispatch }) {
  return next => action => {
    if (isDSA(action)) {
      const payload = action && action.payload ? action.payload : null;
      if (payload && isDSA(payload)) {
        return dispatch(action.payload);
      }
    }

    return next(action);
  };
}
