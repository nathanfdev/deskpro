import { createAction } from "Ampliflux";
import * as Feedback from "DeskPRO/Bundle/AgentBundle/Services/Api/Feedback";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export const feedbackToValidate = createAction(
  "FEEDBACK_TO_VALIDATE",
  () => dispatch => Feedback.toValidate().then(value => value.getData()));

export const commentsToReview = createAction(
  "FEEDBACK_COMMENTS_TO_REVIEW",
  () => dispatch => Feedback.commentsToReview().then(value => value.getData()));

export const feedbackLabels = createAction(
  "FEEDBACK_LABELS",
  () => dispatch => Feedback.getLabels().then(value => value.getData()));

export const feedbackTypes = createAction(
  "FEEDBACK_TYPES",
  () => dispatch => Feedback.getTypes().then(value => value.getData()));

export const feedbackCustomCategories = createAction(
  "FEEDBACK_CUSTOM_CATEGORIES",
  () => dispatch => Feedback.getCustomCategories().then(value => value.getData()));

export const feedbackNew = createAction(
  "FEEDBACK_NEW_STATUS",
  () => dispatch => Feedback.getNew().then(value => value.getData()));


export const feedbackActiveStatus = createAction(
  "FEEDBACK_ACTIVE_STATUS",
  () => dispatch => Feedback.getActive().then(value => value.getData()));

export const feedbackClosedStatus = createAction(
  "FEEDBACK_CLOSED_STATUS",
  () => dispatch => Feedback.getClosed().then(value => value.getData()));

export const feedbackHiddenStatus = createAction(
  "FEEDBACK_HIDDEN_STATUS",
  () => dispatch => Feedback.getHidden().then(value => value.getData()));

export const loadFeedbackList = createAction(
  "FEEDBACK_LIST",
  (query, filters, sort, order) =>
      dispatch => Feedback.getList(query, filters, sort, order).then(value => value.getData())
);

export const changeQueryState = createAction(
  "FEEDBACK_CHANGE_QUERY",
  (trigger, query, sort, order, filters) => {
    trigger(query);
    trigger(loadFeedbackList(query, sort, order, filters));
  }
);

export const getFilterValues = createAction(
  "FEEDBACK_SELECT_FILTER",
  (filterName) => dispatch => Feedback.getFilterValues(filterName).then(value => value.getData())
);

export const setFilterValue = createAction(
  "FEEDBACK_SET_FILTER_VALUE",
  (trigger, filter, value) => dispatch =>    trigger({filter: filter, value: value})
);

export const resetFilterValue = createAction(
  "FEEDBACK_RESET_FILTER_VALUE"
);

export const resetFilters = createAction(
  "FEEDBACK_RESET_FILTERS",
  (trigger, filterAlias, filterName) => {
    trigger({alias: filterAlias, name: filterName, value: ''});
  });

export const setTableSort = createAction(
  "FEEDBACK_SET_TABLE_SORT",
  (trigger, query, sort, order, filters) => {
    trigger({sort: sort, order: order});
    trigger(loadFeedbackList(query, sort, order, filters));
  });

export const toggleViewMode = createAction(
  "FEEDBACK_TOGGLE_VIEW_MODE",
    viewMode => viewMode
);

export const toggleOrder = createAction(
  "FEEDBACK_TOGGLE_ORDER",
  (trigger, query, sort, order, filters) => {
    trigger({order: order});
    trigger(loadFeedbackList(query, sort, order, filters));
  });

export const toggleSort = createAction(
  "FEEDBACK_TOGGLE_SORT",
  (trigger, query, sort, order, filters) => {
    trigger(sort);
    trigger(loadFeedbackList(query, sort, order, filters));
  });

export const storeDisplayFieldsToPersonSetting = createAction(
  "FEEDBACK_STORE_DISPLAY_FIELD_TO_PERSON_SETTING",
  (displayFields) => {
    Feedback.postDisplayFieldsToPersonSetting('feedback_display_fields', displayFields).then(value => value.getData())
  });

export const getDisplayFieldsFromPersonSetting = createAction(
  "FEEDBACK_GET_DISPLAY_FIELD_TO_PERSON_SETTING",
  () => {
    Feedback.getDisplayFieldsFromPersonSetting('feedback_display_fields').then(value => value.getData())
  });

export const changeDisplayFieldsStatus = createAction(
  "FEEDBACK_DISPLAY_FIELD_STATUS",
  (trigger, type, field, status, query, sort, order, filters, listViewFields, tableViewFields) => {
    trigger({type: type, field: field, status: status});
    trigger(storeDisplayFieldsToPersonSetting([{listViewFields: listViewFields, tableViewFields: tableViewFields}]));
    trigger(loadFeedbackList(query, sort, order, filters));
  });
