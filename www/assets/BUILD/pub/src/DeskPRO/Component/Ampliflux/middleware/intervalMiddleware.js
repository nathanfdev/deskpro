import { isDSA } from '../actions/actionUtils';

function getInterval(action) {
  if (!isDSA(action)) {
    return null;
  }

  if (action.meta && action.meta.interval) {
    return parseInt(action.meta.interval, 10);
  }

  return null;
}

/**
 * Schedules an action to dispatch given an interval on action.meta.interval.
 *
 * `dispatch` will return a cancel function.
 *
 * @return {Function} middelware
 */
export function intervalMiddleware() {
  return next => action => {
    const interval = getInterval(action);
    if (!interval) {
      return next(action);
    }

    const iId = setInterval(
      () => next(action),
      interval
    );

    return function cancel() {
      clearInterval(iId);
    };
  };
}
