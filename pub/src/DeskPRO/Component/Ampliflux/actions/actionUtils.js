import isPlainObject from 'lodash/lang/isPlainObject';
import objGet from "lodash/object/get";
import Immutable from "immutable";

/**
 * Get a object or function, see if it has the standard
 * properties defined that tell us its action type.
 *
 * @param {Function/Object} thing  The thing to check. Typically an action (DSA style)
 *                                 or a function created via createAction.
 * @param {Boolean} returnNull True to return null if no action type could be found. Otherwise
 *                             this call will throw an exception.
 * @return {String}
 */
export function getActionType(thing, returnNull = false) {

  // DSA-actions have type property
  if (thing && typeof thing.type === "string") {
    return thing.type;
  // Functions created via createAction have actionType property (old style)
  } else if (thing && typeof thing === "function" && typeof thing.actionType === "string") {
    return thing.actionType;
  // Functions created via createAction have actionType property (new style)
  } else if (thing && typeof thing === "function" && typeof thing.actionType === "string") {
    return thing.type;
  }

  if (typeof thing === "string") {
    return thing;
  }

  if (!returnNull) {
    console.error("Unknown parameter sent to getActionType", thing)
    throw new Error("Unknown parameter sent to getActionType");
  }

  return null;
}

/**
 * Checks to see if action is a DeskPRO-Standard-Action.
 *
 * DSA is:
 * - string type:       [FSA] The action type
 * - mixed payload:     [FSA] The payload
 * - bool error:        [FSA] True if there is an error
 * - object meta:       [FSA/DSA] Optional data, but always an object. (FSA allows it to be anything.)
 * - object sequence:   [DSA] Describing the action if it is in a multi-dispatch sequence (such as async before/after/error etc)
 *
 * @param {mixed} action
 * @return bool
 */
export function isDSA(action) {
  return action && typeof action.type !== 'undefined';
}
