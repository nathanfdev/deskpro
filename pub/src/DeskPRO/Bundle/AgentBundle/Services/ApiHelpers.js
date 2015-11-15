/*
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
export function compileParams(params = {}) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    if (Object.prototype.toString.call(params[key]) === '[object Array]') {
      params[key].forEach(item => compiled.push(key + '[]=' + String(item).replace(/\s/g, '%20')));
    } else if (params[key] !== undefined) {
      const str = String(params[key]);
      if (str !== 'null') {
        compiled.push(key + '=' + str.replace(/\s/g, '%20'));
      }
    }
  }

  return compiled.join('&');
}
