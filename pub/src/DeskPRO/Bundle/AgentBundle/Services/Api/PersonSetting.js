import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';

/**
 * Store display fields to person setting
 * @param {string} settingName    Which kind of person setting will be posted
 * @param {Object} value          Post data
 * @return {object} Promise
 */
export function post(settingName, value) {
  return api.sendPost('DP_API/person_setting', {name: settingName, value: value});
}

/**
 * Update person setting
 * @param {string} settingName    Which kind of person setting will be posted
 * @param {Object} value          Post data
 * @return {object} Promise
 */
export function put(settingName, value) {
  return api.sendPut('DP_API/person_setting', {name: settingName, value: value});
}

/**
 * Get person setting
 * @param {string} settingName    Which kind of person setting
 * @return {object} Promise
 */
export function get(settingName) {
  return api.sendGet('DP_API/person_setting/' + settingName);
}
