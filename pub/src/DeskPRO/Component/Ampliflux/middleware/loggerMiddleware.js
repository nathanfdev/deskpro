import { isDSA, getActionType } from '../actions/actionUtils';

export default function loggerMiddleware({ getState }) {
  return next => action => {
    if (!window.DP_ENABLE_ACTION_LOGGER) {
      return next(action);
    }

    const actionType = isDSA(action) ? getActionType(action) : "ANON";

    if (actionType == "ACTION_REDISPATCH") {
      return next(action);
    }

    if (console.groupCollapsed) {
      console.groupCollapsed("[Dispatch] " + actionType);
    } else {
      console.group("[Dispatch] " + actionType);
    }

    console.debug("Action", action);
    try {
      const result = next(action);
      console.debug("Result", result);
      console.debug("NextState", getState());
      console.groupEnd();
      return result;
    } catch (e) {
      console.error("Error", e);
      console.groupEnd();
      throw e;
    }
  }
}
