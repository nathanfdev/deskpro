import DpApi from "../DpApi";

export function loadPeople(options) {
  if(options.is_me) {
    options.is_me = 1;
  }
  if(options.is_agent) {
    options.is_agent = 1;
  }

  const request = options.length > 0 ? ('?' + compileParams(options)) : '';
  return DpApi.sendGet(`DP_API/people${request}`);
}

export function loadPerson(id) {
  return DpApi.sendGet(`DP_API/people/${id}`);
}

/**
 * Compile parameters into a URL string
 * @param params
 * @returns {string}
 */
function compileParams(params) {
  let compiled = [];

  for (let key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}
