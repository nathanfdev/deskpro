import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

/**
 * Load all departments.
 * @param {Object} options - to modify query
 * @return {object} promise
 */
export function loadDepartments(options = {}) {
  return api.sendGet('DP_API/ticket_departments?' + compileParams(options));
}

export function loadDepartment(id) {
  return api.sendGet(`DP_API/ticket_departments/${id}`);
}

export function load(ids) {
  return api.sendGet('DP_API/ticket_departments?ids=' + ids.join(','));
}

/**
 * Compile parameters into a URL string
 * @param {Object} params - to compile
 * @returns {string} - compiled string
 */
function compileParams(params) {
  let compiled = [];

  for (let key of Object.keys(params)) {
    compiled.push(key + '=' + String(params[key]));
  }

  return compiled.join('&');
}
