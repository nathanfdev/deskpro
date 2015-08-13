import isPlainObject from 'lodash/lang/isPlainObject';

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
