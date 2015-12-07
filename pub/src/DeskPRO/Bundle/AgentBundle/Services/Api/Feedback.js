import DpApi from '../DpApi';
import { compileParams } from '../ApiHelpers';

/*
 * Feedback to validate count
 * @return Promise
 */
export function feedbackToValidate() {
  const query = {
    awaiting_validation: 1
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Feedback comments to review count
 * @return Promise
 */
export function commentsToReview() {
  const query = {
    awaiting_validation: 1
  };

  return DpApi.sendGet('DP_API/feedback_comments/counts?' + compileParams(query));
}

/*
 * @return Promise
 */
export function deleteFeedbackComment(ids) {
  const params = [];
  ids.forEach((id) => {
    params.push('id[]=' + id);
  });
  return DpApi.sendDelete('DP_API/feedback_comments?' + params.join('&'));
}

export function deleteFeedback(ids) {
  const params = [];
  ids.forEach((id) => {
    params.push('id[]=' + id);
  });
  return DpApi.sendDelete('DP_API/feedback?' + params.join('&'));
}

/*
 * Update a feedbackComment
 * @param commentId
 * @param data
 * @return Promise
 */
export function editFeedbackComment(commentId, data) {
  return DpApi.sendPut('DP_API/feedback_comments/' + commentId, data);
}


/*
 * Feedback labels
 * @return Promise
 */
export function getLabels() {
  return DpApi.sendGet('DP_API/feedback_labels');
}

/*
 * Feedback by types with counts
 * @return Promise
 */
export function getTypes() {
  const query = {
    group_by: 'category'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Feedback by custom categories with counts
 * @return Promise
 */
export function getCustomCategories() {
  const query = {
    group_by: 'custom_category'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Get list of filtered feedback
 * @param {object} params Request options
 * @return {object} Promise
 */
export function getList(params) {
  console.log('DP_API/feedback/?include=person,feedback_status_category,custom_data_feedback&' + compileParams(params));
  return DpApi.sendGet('DP_API/feedback/?include=person,feedback_status_category,custom_data_feedback&' + compileParams(params));
}

/*
 * Feedback comments to review list
 * @return Promise
 */
export function commentsToReviewList(params) {
  console.log('DP_API/feedback_comments_list?include=person,feedback&' + compileParams(params));
  return DpApi.sendGet('DP_API/feedback_comments_list?include=person,feedback&' + compileParams(params));
}

export function massAction(params) {
  const ids = [];
  params.ids.forEach((id) => {
    ids.push('id[]=' + id);
  });
  console.log('DP_API/feedback/mass_action?' + ids.join('&'));
  return DpApi.sendPut('DP_API/feedback/mass_action?' + ids.join('&'), params.actions);
}
