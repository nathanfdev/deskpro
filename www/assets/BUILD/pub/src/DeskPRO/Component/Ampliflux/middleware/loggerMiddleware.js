import isPlainObject from 'lodash/isPlainObject';
import Immutable from 'immutable';
import { isDSA, getActionType } from '../actions/actionUtils';

function jsValue(val) {
  if ((/boolean|number|string/).test(typeof val)) {
    return val;
  }

  if (Immutable.Iterable.isIterable(val)) {
    return val.toJS();
  }

  if (isPlainObject(val)) {
    const v = Immutable.fromJS(val);
    if (v) {
      return v.toJS();
    }
  }

  return val;
}

export function loggerMiddleware({ getState }) {
  return next => (action) => {
    if (!window.DP_DEV_MODE) {
      return next(action);
    }

    const actionType = isDSA(action) ? getActionType(action) : '[anon]';
    const sequenceType = action && action.meta && action.meta.sequenceType ? action.meta.sequenceType : null;
    const sequence = action && action.meta && action.meta.sequence ? action.meta.sequence : null;

    const dispatchTitle = actionType + (sequenceType ? ` - ${sequenceType} ${sequence}` : '');

    if (console.groupCollapsed) {
      console.groupCollapsed(`[Dispatch] ${dispatchTitle}`);
    } else {
      console.group(`[Dispatch] ${dispatchTitle}`);
    }

    console.debug('Action', jsValue(action));
    try {
      const result = next(action);
      let isErrorStatus = false;

      if (typeof result !== 'undefined') {
        const jsResult = jsValue(result);
        if (jsResult && jsResult.error && jsResult.error === true) {
          isErrorStatus = true;
          console.error('Result', jsResult);
        }
        /* if no errors, the Result fully coincides with the Action
        else {
          console.debug('Result', jsResult);
        }*/
      }

      console.debug('NextState', jsValue(getState()));
      console.groupEnd();

      if (isErrorStatus) {
        console.warn(`Note: Last action ${actionType} had error status`);
      }
      return result;
    } catch (e) {
      console.groupEnd();
      console.error('Error in above action', e);
      throw e;
    }
  };
}
