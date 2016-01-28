import { getActionType } from './actionUtils';

/**
 * Given a param meant to be an action function,
 * return a real usable function. This helps
 * create default actions if a real function wasn't supplied.
 *
 * @param {any} actionFn The param you want to make sure is a function
 * @return {Function} Returns a usable function
 */
function createActionFn(actionFn) {
  // Default -> whatever is passed to the action, dispatch that
  if (typeof actionFn === 'undefined') {
    return (v) => v;

  // Constant -> the action has a hard-coded value
  } else if (typeof actionFn !== 'function') {
    return () => actionFn;
  }

  // Typical -> The action is a function
  return actionFn;
}


/**
 * Create a DeskPRO action.
 *
 * The function returned will have a special prop:
 *   - fn.actionType:   Is the actionType you specified'
 *
 * @param {String}     actionType   The action type
 * @param {Function}   actionFn     The action method, undefined for a pass-thru, or a constant.
 *                                  The function will be passed the same arguments as the action.
 * @param {Function}   metaFn       The meta function, used to generate values for the 'meta' property of the action.
 *                                  The function will be passed the action (with action.payload being whatever the result of action is),
 *                                  followed by all other args passed to the action.
 * @return {Function} Returns your wrapped action
 */
export function createAction(actionType, actionFn, metaFn) {
  const type         = getActionType(actionType);
  const userActionFn = createActionFn(actionFn);
  const userMetaFn   = typeof metaFn === 'function' ? metaFn : null;

  const finalActionFn = (...args) => {
    const action = {
      type: type,
      error: false,
      meta: {}
    };

    try {
      const payload = userActionFn(...args);

      action.payload = payload;
    } catch (e) {
      action.error = true;
      action.payload = e;
    }

    if (userMetaFn) {
      action.meta = userMetaFn(action, ...args);
    }

    return action;
  };

  finalActionFn.type = type;
  finalActionFn.toString = () => type;

  return finalActionFn;
}
