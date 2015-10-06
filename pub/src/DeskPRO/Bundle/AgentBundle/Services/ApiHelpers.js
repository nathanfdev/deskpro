/*
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
export function compileParams(params = {}) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    var str = String(params[key]);
    if (str !== 'null') {
      compiled.push(key + '=' + str.replace(/\s/g, '%20'));
    }
  }

  return compiled.join('&');
}
