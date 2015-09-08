import isPlainObject from 'lodash/lang/isPlainObject';
import { isDSA, getActionType } from '../actions/actionUtils';
import Immutable from "immutable";

function jsValue(val) {
  if ((/boolean|number|string/).test(typeof val)) {
    return val;
  }

  if (Immutable.Iterable.isIterable(val)) {
    return val.toJS();
  }

  if (isPlainObject(val)) {
    let v = Immutable.fromJS(val);
    if (v) {
      return v.toJS();
    }
  }

  return val;
}

export default function loggerMiddleware({ getState }) {
  return next => action => {
    if (!window.DP_ENABLE_ACTION_LOGGER) {
      return next(action);
    }

    const actionType = isDSA(action) ? getActionType(action) : "ANON";

    if (console.groupCollapsed) {
      console.groupCollapsed("[Dispatch] " + actionType);
    } else {
      console.group("[Dispatch] " + actionType);
    }

    console.debug("Action", jsValue(action));
    try {
      let result = jsValue(next(action));
      if (result && result.error) {
        console.error("Result", result);
      } else {
        console.debug("Result", result);
      }

      console.debug("NextState", jsValue(getState()));
      console.groupEnd();

      if (result && result.error && result.error === true) {
        console.warn("Note: Last action " + actionType + " had error status");
      }

      return result;
    } catch (e) {
      console.groupEnd();
      console.error("Error in above action", e);
      throw e;
    }
  }
}
