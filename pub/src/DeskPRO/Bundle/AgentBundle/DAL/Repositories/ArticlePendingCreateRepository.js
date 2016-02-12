import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * ArticlePendingCreateRepository
 */
export class ArticlePendingCreateRepository extends ApiRepository {
  /**
   * @param assignee
   * @returns {*}
   */
  loadCount(assignee) {
    return this.api.sendGet(`DP_API/${this.url}/counts` + (assignee ? '?assigned_person=' + assignee : ''));
  }
}
