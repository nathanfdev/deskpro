import { isDSA } from '../actions/actionUtils';

export function timerMiddleware(timeProp) {
  return () => next => action => {
    if (isDSA(action)) {
      if (!action.meta) {
        action.meta = {};
      }
      action.meta[timeProp] = new Date();
    }
    return next(action);
  };
}
