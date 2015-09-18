import DpApi from "../DpApi";
import { compileParams } from '../ApiHelpers';

/**
 * Load a generic API endpoint. Only use when you need to get the address from the action
 * @param address
 * @param params
 * @return Promise
 */
export function loadAddress(address, params = {}) {
  if (params.length > 0) {
    address = address + '?' + compileParams(params);
  }

  return DpApi.sendGet('DP_API/' + address);
}

/**
 * Feedback counts
 * @return Promise
 */
export function toValidate() {
  let query = {
    awaiting_validation: 1
  };
  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Feedback comments to review count
 * @return Promise
 */
export function commentsToReview() {
  let query = {
    awaiting_validation: 1
  };

  return DpApi.sendGet('DP_API/feedback_comments/counts?' + compileParams(query));
}

/**
 * Feedback labels
 * @return Promise
 */
export function getLabels() {
  return DpApi.sendGet('DP_API/feedback_labels');
}

/**
 * Feedback by types with counts
 * @return Promise
 */
export function getTypes() {
  let query = {
    group_by: "category"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Feedback by custom categories with counts
 * @return Promise
 */
export function getCustomCategories() {
  let query = {
    group_by: "custom_category"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of new feedback
 * @return Promise
 */
export function getNew() {
  let query = {
    status: "new"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of active feedback
 * @return Promise
 */
export function getActive() {
  let query = {
    status: "active",
    group_by: "status_category"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of closed feedback
 * @return Promise
 */
export function getClosed() {
  let query = {
    status: "closed",
    group_by: "status_category"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Count of hidden feedback
 * @return Promise
 */
export function getHidden() {
  let query = {
    status: "hidden",
    group_by: "hidden_status"
  };

  return DpApi.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/**
 * Store display fields to person setting
 * @return Promise
 */
export function postDisplayFieldsToPersonSetting(settingName, displayFields) {
  return DpApi.sendPost('DP_API/person_setting', {name: settingName, value: displayFields});
}
/**
 * Get display fields from person setting
 * @return Promise
 */
export function getDisplayFieldsFromPersonSetting(settingName) {
  return DpApi.sendGet('DP_API/person_setting/' + settingName);
}

/**
 * Get list of filtered feedback
 * @return Promise
 */
export function getList(query, sort, order, filters) {
  let params = [];
  params.push(compileParams(query));
  params.push('sort=' + sort);
  params.push('order=' + order);
  if (filters.value && filters.value.length > 0) {
    params.push(filters.alias + '=' + filters.value.replace(/\s/g, "%20"));
  }
  //console.log('DP_API/feedback/?' + params.join('&'));
  return DpApi.sendGet('DP_API/feedback/?' + params.join('&'));
}
/**
 * Get values for chosen filter
 * @return Promise
 */
export function getFilterValues(filterName) {
  return DpApi.sendGet('DP_API/feedback/filter?name=' + filterName);
}
