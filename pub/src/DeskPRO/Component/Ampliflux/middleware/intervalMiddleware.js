import { isDSA } from '../actions/actionUtils';

function getInterval(action) {
  if (isDSA(action) && action.meta && action.meta.interval) {
    return action.meta.interval;
  }

  return null;
}

/**
 * Schedules an action to dispatch repeatedly based on action.meta.interval.
 *
 * `dispatch` will return a cancel function.
 */
export default function intervalMiddleware() {
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
    }
  }
}
