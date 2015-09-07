/**
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
export function compileParams(params) {
  let compiled = [];

  for (let key of Object.keys(params)) {
    var str = String(params[key]);
    if ('null' !== str) {
      compiled.push(key + '=' + str.replace(/\s/g, "%20"));
    }
  }

  return compiled.join('&');
}
