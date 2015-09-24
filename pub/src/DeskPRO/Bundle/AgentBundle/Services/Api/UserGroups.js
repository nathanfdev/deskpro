import DpApi from '../DpApi';

/**
 * Get user counts by groups
 *
 * @return {Promise}
 */
export function loadCounts() {
  return DpApi.sendGet('DP_API/user_groups/counts');
}
