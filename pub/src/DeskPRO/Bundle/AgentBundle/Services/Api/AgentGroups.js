import DpApi from "../DpApi";

/**
 * Load all filter sets.
 * @return Promise.
 */
export function loadAll() {
  return DpApi.sendGet('DP_API/agent_groups');
}

/**
 * Count the number of tickets in each filter set and each filter within the filter set.
 * @return Promise.
 */
export function loadUserGroup(agent_group_id = 'all') {
  return DpApi.sendGet('DP_API/agent_groups/' + agent_group_id);
}
