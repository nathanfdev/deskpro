import DpApi from "../DpApi";

/** Load all departments. */
export function loadDepartments(options = {}) {
  return DpApi.sendGet('DP_API/ticket_departments?' + compileParams(options)).then(httpResult => httpResult.getData());
}

export function loadDepartment(id) {
  return DpApi.sendGet(`DP_API/ticket_departments/${id}`).then(httpResult => httpResult.getData());
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
