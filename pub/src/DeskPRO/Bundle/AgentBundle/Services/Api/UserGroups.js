import DpApi from "../DpApi";

/**
 * Load all filter sets.
 * @return Promise.
 */
export function loadAll() {
  return DpApi.sendGet('DP_API/user_groups');
}

/**
 * Count the number of tickets in each filter set and each filter within the filter set.
 * @return Promise.
 */
export function loadUserGroup(user_group_id = 'all') {
  return DpApi.sendGet('DP_API/user_groups/' + user_group_id);
}
