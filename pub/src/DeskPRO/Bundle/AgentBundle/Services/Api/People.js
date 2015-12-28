import DpApi from '../DpApi';

/**
 * Compile parameters into a URL string
 * @param {Object} params - to compile
 * @return {string} - compiled string
 */
function compileParams(params) {
  const compiled = [];

  for (const key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}

/**
 * @return {Promise} promise
 */
export function loadMe() {
  return DpApi.sendGet('DP_API/me');
}

export function loadPeople(options) {
  if (options.is_me) {
    options.is_me = 1;
  }
  if (options.is_agent) {
    options.is_agent = 1;
  }

  const request = Object.keys(options).length > 0 ? ('?' + compileParams(options)) : '';
  console.log(`DP_API/people${request}`);
  return DpApi.sendGet(`DP_API/people${request}`);
}

export function loadPerson(id) {
  return DpApi.sendGet(`DP_API/people/${id}`);
}
