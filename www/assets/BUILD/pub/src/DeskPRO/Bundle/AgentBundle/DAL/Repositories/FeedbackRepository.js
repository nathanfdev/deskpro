import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * FeedbackRepository
 */
export class FeedbackRepository extends ApiRepository {
  /**
   * @param params
   * @param include
   * @returns {*}
   */
  search(params, include = 'person,feedback_status_category,custom_data_feedback') {
    return super.search(params, include);
  }

  /**
   * @returns {*}
   */
  loadFeedbackToValidate() {
    return this.api.sendGet(`DP_API/${this.url}/counts?awaiting_validation=1`);
  }

  /**
   * @returns {*}
   */
  loadCustomCategories() {
    return this.api.sendGet(`DP_API/${this.url}/counts?group_by=custom_category`);
  }
}
