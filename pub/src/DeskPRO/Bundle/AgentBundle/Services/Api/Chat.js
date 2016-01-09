import DpApi from '../DpApi';
import { compileParams } from '../ApiHelpers';

/**
 * @param groupBy
 * @param agent
 * @return Promise
 */
export function loadCounts(groupBy, agent) {
  return DpApi.sendGet('DP_API/user_chats/counts?group_by=' + groupBy + (agent ? '&agent=' + agent : ''));
}

/**
 * @param filters
 * @return Promise
 */
export function load(filters) {
  console.log('DP_API/user_chats' + (filters ? '?' + compileParams(filters) : ''));
  return DpApi.sendGet('DP_API/user_chats' + (filters ? '?' + compileParams(filters) : ''));
}
