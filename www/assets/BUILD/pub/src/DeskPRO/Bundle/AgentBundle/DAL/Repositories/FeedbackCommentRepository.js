import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * FeedbackCommentRepository
 */
export class FeedbackCommentRepository extends ApiRepository {

  /*
   * Feedback comments to review list
   * @return Promise
   */
  commentsToReviewList(params) {
    return this.api.sendGet('DP_API/feedback_comments_list?include=person,feedback&' + this.compileParams(params));
  }

  /**
   * @returns {*}
   */
  commentsToReview() {
    return this.api.sendGet(`DP_API/${this.url}/counts?awaiting_validation=1`);
  }
}
