import { isDSA } from '../actions/actionUtils';
import uniqueId from 'lodash/utility/uniqueId';

/**
 * Adds a unique ID to every action. Useful for logging etc.
 */
export default function guidMiddleware() {
  return next => action => {
    if (isDSA(action)) {
      if (!action.meta) {
        action.meta = {};
      }
      action.meta.guid = uniqueId();
    }
    return next(action);
  }
}
