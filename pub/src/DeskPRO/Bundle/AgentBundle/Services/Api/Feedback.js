import DpApi from '../DpApi';
import { compileParams } from '../ApiHelpers';

/*
 * Feedback counts
 * @return Promise
 */
export function toValidate() {
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
export function deleteFeedbackComment(id) {
  return DpApi.sendDelete('DP_API/feedback_comments/' + id);
}

/*
 * Feedback comments to review list
 * @return Promise
 */
export function commentsToReviewList(params) {
  const paramsEncoded = [];
  if (params.sort && params.order) {
    paramsEncoded.push('sort=' + params.sort);
    paramsEncoded.push('order=' + params.order);
  }
  paramsEncoded.push('awaiting_validation=1');

  return DpApi.sendGet('DP_API/feedback_comments_list?' + paramsEncoded.join('&'));
}

/*
 * Update a feedbackComment
 * @param commentId
 * @param data
 * @return Promise
 */
export function editComment(commentId, data) {
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
 * Count of new feedback
 * @return Promise
 */
export function getNew() {
  const query = {
    status: 'new'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Count of active feedback
 * @return Promise
 */
export function getActive() {
  const query = {
    status: 'active',
    group_by: 'status_category'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Count of closed feedback
 * @return Promise
 */
export function getClosed() {
  const query = {
    status: 'closed',
    group_by: 'status_category'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Count of hidden feedback
 * @return Promise
 */
export function getHidden() {
  const query = {
    status: 'hidden',
    group_by: 'hidden_status'
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Get list of filtered feedback
 * @param {object} params Request options
 * @return {object} Promise
 */
export function getList(params) {
  console.log('DP_API/feedback/?' + compileParams(params));
  return DpApi.sendGet('DP_API/feedback/?' + compileParams(params));
}

/**
 * Get values for chosen filter
 * @return {object} Promise
 */
export function getFilterValues(filterName) {
  return DpApi.sendGet('DP_API/feedback/filter?name=' + filterName);
}
