import { isDSA } from '../actions/actionUtils';
import uuid from "node-uuid";

export default function timerMiddleware(timeProp) {
  return () => next => action => {
    if (isDSA(action)) {
      if (!action.meta) {
        action.meta = {};
      }
      action.meta[timeProp] = new Date();
    }
    return next(action);
  }
}
