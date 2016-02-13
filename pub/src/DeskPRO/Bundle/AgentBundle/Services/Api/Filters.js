import DpApi from "../DpApi";

/**
 * Loads all filters in a filter set.
 * @param int filter_set_id is the ID of the filter set to which the filters belong.
 * @return Promise.
 */
export function loadFiltersInSet(filter_set_id) {
  return DpApi.sendGet('DP_API/ticket_filter_sets/' + filter_set_id + '/filters');
}

/**
 * Loads the ticket counts for a specific filter; optionally grouped by something.
 * @param int filter_id is the ID of the filter you want
 * @param string groupings is a list of groupings you want the counts for.
 * @return Promise.
 */
export function ticketCountsForFilter(filter_id, ...groupings) {
  let group_url_bit = '';
  if(groupings) {
    group_url_bit = '?group_by=' + groupings.join(',');
  }
  return DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/count' + group_url_bit);
}

/**
 * Loads all tickets for a filter.
 * @param int filter_id is the ID of the filter you're interested in
 * @return Promise.
 */
export function loadTickets(filter_id) {
  return DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/tickets');
}
