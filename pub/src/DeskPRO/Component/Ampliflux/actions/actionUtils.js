import isPlainObject from 'lodash/lang/isPlainObject';

/**
 * Get a object or function, see if it has the standard
 * properties defined that tell us its action type.
 *
 * @param {Function/Object} thing  The thing to check. Typically an action (DSA style)
 *                                 or a function created via createAction.
 * @return {String}
 */
export function getActionType(thing) {
  // DSA-actions have type property
  if (thing && typeof thing.type === "string") {
    thing = thing.type;
  // Functions created via createAction have actionType property
  } else if (thing && typeof thing.actionType === "string") {
    thing = thing.actionType;
  }

  if (typeof thing !== "string") {
    console.error("Unknown parameter sent to getActionType", thing);
    throw new Error("Unknown parameter sent to getActionType");
  }

  return thing;
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
 * - string parentType: [DSA] If the action was re-dispatched, then this will contain the original type.
 *
 * @param {mixed} action
 * @param {bool}  strict
 * @return bool
 */
export function isDSA(action, strict) {
  return (
    isPlainObject(action)
    && typeof action.type !== 'undefined'
  );
}
