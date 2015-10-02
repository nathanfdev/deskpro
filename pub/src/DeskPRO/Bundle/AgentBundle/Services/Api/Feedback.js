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
 * Feedback comments to review list
 * @return Promise
 */
export function commentsToReviewList() {
  const query = {
    awaiting_validation: 1
  };

  return DpApi.sendGet('DP_API/feedback_comments_list?' + compileParams(query));
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

/*
 * Store display fields to person setting
 * @return Promise
 */
export function postDisplayFieldsToPersonSetting(settingName, displayFields) {
  return DpApi.sendPost('DP_API/person_setting', {name: settingName, value: displayFields});
}

/*
 * Get display fields from person setting
 * @return Promise
 */
export function getDisplayFieldsFromPersonSetting(settingName) {
  return DpApi.sendGet('DP_API/person_setting/' + settingName);
}

/*
 * Get list of filtered feedback
 * @return Promise
 */
export function getList(params) {
  let paramsEncoded = [];
  paramsEncoded.push('sort=' + params.sort);
  paramsEncoded.push('order=' + params.order);
  if (params.group) {
    paramsEncoded.push(params.group.name + '=' + String(params.group.value).replace(/\s/g, "%20"));
  }
  if (params.filters.value && params.filters.value.length > 0) {
    paramsEncoded.push(params.filters.alias + '=' + params.filters.value.replace(/\s/g, "%20"));
  }
  // console.log('DP_API/feedback/?' + paramsEncoded.join('&'));
  return DpApi.sendGet('DP_API/feedback/?' + paramsEncoded.join('&'));
}
/*
 * Get values for chosen filter
 * @return Promise
 */
export function getFilterValues(filterName) {
  return DpApi.sendGet('DP_API/feedback/filter?name=' + filterName);
}
