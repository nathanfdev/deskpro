import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/**
 * @param groupBy
 * @param agent
 * @return Promise
 */
export function loadCounts(groupBy, agent) {
  return api.sendGet('DP_API/user_chats/counts?group_by=' + groupBy + (agent ? '&agent=' + agent : ''));
}

/**
 * @param filters
 * @return Promise
 */
export function load(filters) {
  console.log('DP_API/user_chats?include=person,agent,department' + (filters ? '&' + compileParams(filters) : ''));
  return api.sendGet('DP_API/user_chats?include=person,agent,department' + (filters ? '&' + compileParams(filters) : ''));
}
