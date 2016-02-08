import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

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
