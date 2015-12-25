import DpApi from '../../DpApi';

/**
 * @param assignee
 * @return Promise
 */
export function loadCount(assignee) {
  return DpApi.sendGet(
    'DP_API/article_pending_create/counts'
    + (assignee ? '?assigned_person=' + assignee : '')
  );
}

