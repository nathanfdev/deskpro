import { api } from '../../DpApi';

/**
 * @param assignee
 * @return Promise
 */
export function loadCount(assignee) {
  return api.sendGet(
    'DP_API/article_pending_create/counts'
    + (assignee ? '?assigned_person=' + assignee : '')
  );
}
