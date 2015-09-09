import { isDSA } from '../actions/actionUtils';
import uniqueId from 'lodash/utility/uniqueId';

function getDelay(action) {
  if (!isDSA(action)) {
    return null;
  }

  if (action.meta && action.meta.delay) {
    return parseInt(action.meta.delay);
  }

  return null;
}

/**
 * Schedules an action to dispatch given a timout on action.meta.delay.
 *
 * `dispatch` will return a cancel function.
 */
export default function timeoutMiddleware() {
  return next => action => {
    const delay = getDelay(action);
    if (!delay) {
      return next(action);
    }

    const tId = setTimeout(
      () => next(action),
      delay
    );

    return function cancel() {
      clearTimeout(tId);
    };
  }
}
