import Immutable from 'immutable';

/**
 * @type {RegExp} Reserved chars regex
 */
const reservedCharsRegex = /[\.\-,:;]/g;

/**
 * Validates state to not contain reserverd characters
 *
 * @param {Immutable.List} state State to validate
 * @return {void}
 */
function ensureNoReservedChars(state) {
  state.forEach((data, component) => {
    if (reservedCharsRegex.test(component)) {
      throw new Error(`Component identifier "${component}" contains a reserved char`);
    }
    data.forEach((value, key) => {
      if (reservedCharsRegex.test(key)) {
        throw new Error(`Component state key "${key}" contains a reserved char`);
      }
      if (value.forEach) {
        value.forEach(subValue => {
          if (reservedCharsRegex.test(subValue)) {
            throw new Error(`Component state value "${subValue}" contains a reserved char`);
          }
        });
      } else {
        if (reservedCharsRegex.test(value)) {
          throw new Error(`Component state value "${value}" contains a reserved char`);
        }
      }
    });
  });
}

/**
 * Ensures all component states are objects
 *
 * @param {Immutable.List} state State to validate
 * @return {void}
 */
function ensureNoScalarState(state) {
  state.forEach(data => {
    if (typeof data !== 'object') {
      throw new Error('Each component state stored in URL must be an object!');
    }
  });
}

/**
 * Encodes component state into string
 *
 * @param {*} state Component state
 * @returns {string} Encoded component state
 */
function componentStateToString(state) {
  let string = '';
  state.forEach((value, key) => {
    const encodedValue = value.join ? value.join(',') : value;
    string += `${key}-${encodedValue};`;
  });

  return string.slice(0, -1);
}

/**
 * Encodes components state into a human readable string
 *
 * @param {Immutable.List} state State object
 * @returns {string} Encoded state
 */
export function stateToString(state) {
  ensureNoScalarState(state);
  ensureNoReservedChars(state);

  let string = '';
  state.forEach((data, component) => string += `${component}:${componentStateToString(data)}.`);

  return string.slice(0, -1);
}

/**
 * Decode component state from string
 *
 * @param string Encoded component state
 * @returns {{}} Decoded component state
 */
function componentStateFromString(string) {
  if (!string) {
    return {};
  }

  const state = {};
  const parts = string.split(';');
  parts.forEach(part => {
    const [key, value] = part.split('-');
    if (!key || !value) {
      return null;
    }

    let decodedValue;
    if (value.indexOf(',') > -1) {
      decodedValue = value.split(',');
      decodedValue = decodedValue.map(item => (item.match(/^\d+$/) ? Number(item) : item));
    } else {
      decodedValue = value.match(/^\d+$/) ? Number(value) : value;
    }

    state[key] = decodedValue;
  });

  return state;
}

/**
 * Decodes stateToString() string into an Immutable object
 *
 * @param {string} string Encoded state
 * @returns {Immutable.List} Decoded state
 */
export function stateFromString(string) {
  const state = {};

  const components = string.split('.');
  components.forEach((item) => {
    const [component, data] = item.split(':');
    state[component] = componentStateFromString(data);
  });

  return Immutable.fromJS(state);
}

/**
 * Sanitize data to safely put into URL state
 *
 * @param {*} data Data to sanitize
 * @returns {*} Data safe tu put into URL state
 */
export function urlSanitize(data) {
  return data.replace(/\s/g, '_').replace(reservedCharsRegex, '_');
}
