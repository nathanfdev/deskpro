import DpApi from "../../DpApi";

/**
 * @param target
 * @param groupBy
 * @return Promise
 */
export function loadCounts(target, groupBy) {
  if (['articles', 'news', 'downloads'].indexOf(target) === -1) {
    throw 'Unknown content type ' + target;
  }

  return DpApi.sendGet('DP_API/' + target + '/counts?group_by=' + groupBy);
}

/**
 * @return Promise
 */
export function loadCategories() {
  return DpApi.sendGet('DP_API/content_categories');
}