import DpApi from '../../DpApi';

/**
 * @param target
 * @param groupBy
 * @param group
 * @return Promise
 */
export function load(target, groupBy, group) {
  return DpApi.sendGet('DP_API/' + validateTarget(target) + '?' + groupBy + '=' + group);
}

/**
 * @param target
 * @param groupBy
 * @return Promise
 */
export function loadCounts(target, groupBy) {
  return DpApi.sendGet('DP_API/' + validateTarget(target) + '/counts?group_by=' + groupBy);
}

/**
 * @return Promise
 */
export function loadCategories() {
  return DpApi.sendGet('DP_API/content_categories');
}

/**
 * @param target
 * @param author
 * @return Promise
 */
export function loadDraftsCount(target, author) {
  return DpApi.sendGet(
    'DP_API/' + validateTarget(target) + '/counts?status=hidden&hidden_status=draft'
    + (author ? '&author=' + author : '')
  );
}

/**
 * @param assignee
 * @return Promise
 */
export function loadPendingCount(assignee) {
  return DpApi.sendGet(
    'DP_API/article_pending_create/counts'
    + (assignee ? '?assigned_person=' + assignee : '')
  );
}

/**
 * Validates and returns target content
 *
 * @param target
 * @return {*}
 */
export function validateTarget(target) {
  if (['articles', 'news', 'downloads'].indexOf(target) === -1) {
    throw 'Unknown content type ' + target;
  }

  return target;
}