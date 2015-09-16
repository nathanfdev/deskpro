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
 * @return Promise
 */
export function loadUsersTotalCount() {
  return DpApi.sendGet('DP_API/people/counts?is_agent=0&is_deleted=0');
}

/**
 * @return Promise
 */
export function loadAgentsTotalCount() {
  return DpApi.sendGet('DP_API/people/counts?is_agent=1&is_deleted=0');
}

/**
 * @param ids
 * @return Promise
 */
export function loadNames(ids) {
  return DpApi.sendGet('DP_API/people/names?ids=' + ids.join(','));
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
