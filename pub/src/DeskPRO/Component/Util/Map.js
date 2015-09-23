import objGet from 'lodash/object/get';
import isArray from 'lodash/lang/isArray';
import Immutable from 'immutable';

/**
 * Given an array of objects, create an object keyed by a property in one of the sub-arrays.
 * Basically converts [{id: x}, {id: y}] into {x: {id: x}, y: {id: y}}.
 *
 * @param {Array}  arrayVal   The array of values
 * @param {String} keyProp    The key to use. Use an array to find 'deep' keys.
 * @return {Object} Your objects, keyed by keyProp
 */
export function mapKeyedFromArray(arrayVal, keyProp) {
  const isDeepKey = isArray(keyProp);
  return Immutable.Map().withMutations(map => {
    arrayVal.forEach(v => {
      const k = isDeepKey ? objGet(v, keyProp) : v[keyProp];
      if (k !== null && typeof k !== 'undefined') {
        map.set(k, Immutable.fromJS(v));
      }
    });
  });
}

/**
 * Reduces map of objects {id: {...}} to map of objects' property {id: property}.
 *
 * @param {String} property The selected property
 * @param {Object} map      Objects map
 * @return {Object} {id: property}
 */
export function reduceMapToProperty(property, map) {
  const reduced = {};
  Object.keys(map).forEach(key => reduced[key] = map[key][property]);

  return reduced;
}