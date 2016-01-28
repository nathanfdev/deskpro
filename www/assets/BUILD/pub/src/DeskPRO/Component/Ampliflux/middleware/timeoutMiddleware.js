import { isDSA } from '../actions/actionUtils';

function getDelay(action) {
  if (!isDSA(action)) {
    return null;
  }

  if (action.meta && action.meta.delay) {
    return parseInt(action.meta.delay, 10);
  }

  return null;
}

/**
 * Schedules an action to dispatch given a timout on action.meta.delay.
 *
 * `dispatch` will return a cancel function.
 *
 * @return {Function} middleware
 */
export function timeoutMiddleware() {
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
  };
}
