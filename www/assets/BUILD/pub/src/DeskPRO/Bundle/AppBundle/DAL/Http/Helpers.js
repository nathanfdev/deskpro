/*
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
export function compileParams(value = {}, prefix) {
  const compiled = [];

  if (typeof value === 'object') {
    Object.keys(value).forEach(key => compiled.push(compileParams(value[key], prefix ? `${prefix}[${key}]` : key)));
  } else if (prefix) {
    if (Object.prototype.toString.call(value) === '[object Array]') {
      value.forEach(item => compiled.push(compileParams(item, `${prefix}[]`)));
    } else if (value !== null) {
      const str = String(value);
      compiled.push(`${encodeURIComponent(prefix)}=${encodeURIComponent(str)}`);
    }
  }

  return compiled.join('&');
}
