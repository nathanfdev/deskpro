import uuid from 'uuid';
import { isDSA } from '../actions/actionUtils';

/**
 * Adds a unique ID to every action. Useful for logging etc.
 * @return {Function} middleware
 */
export function guidMiddleware() {
  return next => (action) => {
    if (isDSA(action)) {
      if (!action.meta) {
        action.meta = {};
      }
      action.meta.guid = uuid();
    }
    return next(action);
  };
}
