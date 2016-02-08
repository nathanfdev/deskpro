import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * Load all filter sets.
 * @return Promise.
 */
export function loadAll() {
  return api.sendGet('DP_API/agent_groups');
}

/**
 * Count the number of tickets in each filter set and each filter within the filter set.
 * @return Promise.
 */
export function loadUserGroup(agent_group_id = 'all') {
  return api.sendGet('DP_API/agent_groups/' + agent_group_id);
}
