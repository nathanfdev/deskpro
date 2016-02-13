import { ApiRepository } from 'DeskPRO/Bundle/AppBundle/DAL';

/**
 * FeedbackCommentRepository
 */
export class FeedbackCommentRepository extends ApiRepository {
  /**
   * @param ids
   * @returns {*}
   */
  approveFeedbackComment(ids) {
    const params = [];
    ids.forEach((id) => {
      params.push('id[]=' + id);
    });
    return this.api.sendPatch(`DP_API/${this.url}/approve?` + params.join('&'));
  }

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
