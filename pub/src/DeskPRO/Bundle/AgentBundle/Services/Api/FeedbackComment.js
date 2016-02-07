import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/*
 * Feedback comments to review count
 * @return Promise
 */
export function commentsToReview() {
  const query = {
    awaiting_validation: 1
  };

  return api.sendGet('DP_API/feedback_comments/counts?' + compileParams(query));
}

/*
 * @return Promise
 */
export function deleteFeedbackComment(ids) {
  const params = [];
  ids.forEach((id) => {
    params.push('id[]=' + id);
  });
  return api.sendDelete('DP_API/feedback_comments?' + params.join('&'));
}

export function approveFeedbackComment(ids) {
  const params = [];
  ids.forEach((id) => {
    params.push('id[]=' + id);
  });
  return api.sendPatch('DP_API/feedback_comments/approve?' + params.join('&'));
}

/*
 * Update a feedbackComment
 * @param commentId
 * @param data
 * @return Promise
 */
export function editFeedbackComment(commentId, data) {
  return api.sendPut('DP_API/feedback_comments/' + commentId, data);
}

/*
 * Feedback comments to review list
 * @return Promise
 */
export function commentsToReviewList(params) {
  console.log('DP_API/feedback_comments_list?include=person,feedback&' + compileParams(params));
  return api.sendGet('DP_API/feedback_comments_list?include=person,feedback&' + compileParams(params));
}
