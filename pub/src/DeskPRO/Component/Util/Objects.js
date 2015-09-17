import objGet from 'lodash/object/get';
import isArray from 'lodash/lang/isArray';

/**
 * Given an array of objects, create an object keyed by a property in one of the sub-arrays.
 * Basically converts [{id: x}, {id: y}] into {x: {id: x}, y: {id: y}}.
 *
 * @param {Array}  arrayVal   The array of values to work on
 * @param {String} keyProp    The key to use. Use an array to find 'deep' keys.
 * @return {Object} Your objects, keyed by keyProp
 */
export function objectKeyedFromArray(arrayVal, keyProp) {
  const result = {};
  const isDeepKey = isArray(keyProp);
  arrayVal.forEach(v => {
    const k = isDeepKey ? objGet(v, keyProp) : v[keyProp];
    if (k !== null && typeof k !== 'undefined') {
      result[k] = v;
    }
  });

  return result;
}
