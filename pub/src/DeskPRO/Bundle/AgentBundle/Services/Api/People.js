import DpApi from '../DpApi';
import { compileParams } from '../ApiHelpers';

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

  const request = Object.keys(options).length > 0 ? ('&' + compileParams(options)) : '';
  console.log(`DP_API/people?include=organization,usergroup,language${request}`);
  return DpApi.sendGet(`DP_API/people?include=organization,usergroup,language${request}`);
}

export function loadPerson(id) {
  return DpApi.sendGet(`DP_API/people/${id}`);
}
