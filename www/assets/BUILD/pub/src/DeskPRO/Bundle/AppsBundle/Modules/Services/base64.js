/**
 * @param {string} payload
 * @return {string}
 */
export function encode(payload) {
  if (window && typeof window.btoa === 'function') {
    return window.btoa(payload);
  }

  if (Buffer && typeof Buffer.from === 'function') {
    return Buffer.from(payload).toString('base64');
  }

  throw new Error('no base64 encoding method available');
}

/**
 * @param {string|object} payload
 * @return {string}
 */
export function encodeJSON(payload) {
  return encode(JSON.stringify(payload));
}

export function decode(payload) {
  if (window && typeof window.atob === 'function') {
    return window.atob(payload);
  }

  if (Buffer && typeof Buffer.from === 'function') {
    return Buffer.from(payload).toString('utf8');
  }

  throw new Error('no base64 decoding method available');
}

/**
 * @param {string} payload
 * @return {*}
 */
export function decodeJSON(payload) {
  return JSON.parse(decode(payload));
}
