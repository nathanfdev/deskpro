import objGet from 'lodash/get';
import isArray from 'lodash/isArray';
import isObject from 'lodash/isObject';
import Immutable from 'immutable';
import warning from 'warning';
import invariant from 'invariant';

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

  // Already an Immutable object
  if (Immutable.Iterable.isIterable(arrayVal)) {
    return Immutable.Map().withMutations((map) => {
      arrayVal.forEach((v) => {
        const k = isDeepKey ? v.getIn(keyProp) : v.get(keyProp);
        if (k !== null && typeof k !== 'undefined') {
          map.set(k, Immutable.fromJS(v));
        } else if (__DEV__) { // eslint-disable-line no-undef
          warning(
              true, 'mapKeyedFromArray() the key property %s doesn\'t exist in %s from %s', keyProp, v, arrayVal);
        }
      });
    });

    // A POJO
  } else if (isObject(arrayVal)) {
    return Immutable.Map().withMutations((map) => {
      Object.keys(arrayVal).forEach((key) => {
        const v = arrayVal[key];
        const k = isDeepKey ? objGet(v, keyProp) : v[keyProp];
        if (k !== null && typeof k !== 'undefined') {
          map.set(k, Immutable.fromJS(v));
        } else if (__DEV__) { // eslint-disable-line no-undef
          warning(
              true, 'mapKeyedFromArray() the key property %s doesn\'t exist in %s from %s', keyProp, v, arrayVal);
        }
      });
    });

    // An array
  }
  return Immutable.Map().withMutations((map) => {
    arrayVal.forEach((v) => {
      const k = isDeepKey ? objGet(v, keyProp) : v[keyProp];
      if (k !== null && typeof k !== 'undefined') {
        map.set(k, Immutable.fromJS(v));
      } else if (__DEV__) { // eslint-disable-line no-undef
        warning(
            true, 'mapKeyedFromArray() the key property %s doesn\'t exist in %s from %s', keyProp, v, arrayVal);
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
  Object.keys(map).forEach((key) => {
    if (__DEV__) { // eslint-disable-line no-undef
      warning(
        !map[key][property],
        'reduceMapToProperty() the key property %s doesn\'t exist in %s from %s',
        property, map[key], map
      );
    }
    reduced[key] = map[key][property];
  });

  return reduced;
}

/**
 * Converts {1: {x: 'x1'}, 2: {x: 'x2'}} into {1: 'x1', 2: 'x2'}
 *
 * @param  {string}        property Target property
 * @param  {Immutable.Map} obj Map to be reduced
 * @return {Immutable.Map} Reduced map
 */
export function reduceImmutableToProperty(property, obj) {
  invariant(
    Immutable.Iterable.isIterable(obj),
    'reduceImmutableToProperty() 2nd arg must be an Immutable.Iterable. Got %s',
    obj
  );

  return obj.map((el) => {
    if (__DEV__) { // eslint-disable-line no-undef
      warning(
        el.has(property),
        'reduceImmutableToProperty() the key property %s doesn\'t exist in %s from %s',
        property, el, obj
      );
    }

    return el.get(property);
  });
}

/**
 * Converts [{a: 'a1', b: 'b1'}, {a: 'a2', b: 'b2'}] into {a1: 'b1', a2: 'b2'}
 *
 * @param  {string}             keyProp The key property
 * @param  {string}             valProp The value property
 * @param  {Immutable.Iterable} target  Target imutable iterable
 * @return {Immutable.Map} Result amp
 */
export function toPropsMap(keyProp, valProp, target) {
  invariant(Immutable.Iterable.isIterable(target), 'toPropsMap() target must be an Immutable instance. Got %s', target);

  let map = new Immutable.Map();
  target.forEach((el) => {
    if (__DEV__) { // eslint-disable-line no-undef
      warning(
        el.has(keyProp),
        'toPropsMap() the key property %s doesn\'t exist in %s from %s',
        keyProp, el, target
      );
      warning(
        el.has(valProp),
        'toPropsMap() the value property %s doesn\'t exist in %s from %s',
        valProp, el, target
      );
    }

    map = map.set(el.get(keyProp), el.get(valProp));
  });

  return map;
}

export function sortByAttribute(a, b, attribute) {
  const attrA = a.get(attribute).toLowerCase();
  const attrB = b.get(attribute).toLowerCase();
  if (attrA > attrB) {
    return 1;
  } else if (attrA < attrB) {
    return -1;
  }
  return 0;
}
