import { isDSA } from '../actions/actionUtils';
import uuid from "node-uuid";
import Immutable from "immutable";

/**
 * Adds a unique ID to every action. Useful for logging etc.
 */
export default function guidMiddleware() {
  return next => action => {
    if (isDSA(action)) {
      if (Immutable.Map.isMap(action)) {
        action = action.setIn(['meta', 'guid'], uuid());
      } else {
        if (!action.meta) {
          action.meta = {};
        }
        action.meta.guid = uuid();
      }
    }
    return next(action);
  }
}
