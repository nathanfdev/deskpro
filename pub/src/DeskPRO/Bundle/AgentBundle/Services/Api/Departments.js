import DpApi from "../DpApi";

/** Load all departments. */
export function loadDepartments(options = {}) {
  return DpApi.sendGet('DP_API/departments?' + compileParams(options));
}

export function loadDepartment(id) {
  return DpApi.sendGet(`DP_API/departments/${id}`);
}

export function load(ids) {
  return DpApi.sendGet('DP_API/departments?ids=' + ids.join(','));
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
