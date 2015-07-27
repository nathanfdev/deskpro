import DpApi from "../DpApi";

/**
 * Load all filter sets.
 * @return Promise.
 */
export function loadAll() {
  return DpApi.sendGet('DP_API/ticket_filter_sets');
}

/**
 * Count the number of tickets in each filter set and each filter within the filter set.
 * @return Promise.
 */
export function count(filter_set_id = 'all') {
  return DpApi.sendGet('DP_API/ticket_filter_sets/' + filter_set_id + '/counts');
}
