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
 * Schedules an action to dispatch given a timout on action.meta.delay.
 *
 * `dispatch` will return a cancel function.
 *
 * @return {Function} middelware
 */
export default function intervalMiddleware() {
  return next => action => {
    const delay = getInterval(action);
    if (!delay) {
      return next(action);
    }

    const iId = setInterval(
      () => next(action),
      interval
    );

    return function cancel() {
      clearInterval(iId);
    };
  }
}
