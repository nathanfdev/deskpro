import { api } from 'DeskPRO/Bundle/AppBundle/DAL/Http/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';

/*
 * Feedback to validate count
 * @return Promise
 */
export function feedbackToValidate() {
  const query = {
    awaiting_validation: 1
  };

  return api.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Feedback labels
 * @return Promise
 */
export function getLabels() {
  return api.sendGet('DP_API/feedback_labels');
}

/*
 * Feedback by types with counts
 * @return Promise
 */
export function getTypes() {
  const query = {
    group_by: 'category'
  };

  return api.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Feedback by custom categories with counts
 * @return Promise
 */
export function getCustomCategories() {
  const query = {
    group_by: 'custom_category'
  };

  return api.sendGet('DP_API/feedback/counts?' + compileParams(query));
}

/*
 * Get list of filtered feedback
 * @param {object} params Request options
 * @return {object} Promise
 */
export function getList(params) {
  console.log('DP_API/feedback/?include=person,feedback_status_category,custom_data_feedback&' + compileParams(params));
  return api.sendGet('DP_API/feedback/?include=person,feedback_status_category,custom_data_feedback&' + compileParams(params));
}
