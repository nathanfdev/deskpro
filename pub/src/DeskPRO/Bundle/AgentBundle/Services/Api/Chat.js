import DpApi from "../DpApi";

/**
 * @param groupBy
 * @param agent
 * @return Promise
 */
export function loadCounts(groupBy, agent) {
  return DpApi.sendGet('DP_API/user_chats/counts?group_by=' + groupBy + (agent ? '&agent=' + agent : ''));
}