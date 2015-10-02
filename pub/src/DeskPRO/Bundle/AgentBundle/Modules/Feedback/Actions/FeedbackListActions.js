import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadFeedbackCommentsCounter } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCommentsActions';
import { loadFeedbackStatuses } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackStatusesActions';
import { sortingDataSelector, filterDataSelector } from '../Selectors/list';
import { groupDataSelector } from '../Selectors/nav';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const getAuthors = createAction(
  'FEEDBACK_GET_AUTHORS',
    feedback => dispatch => {
    let ids    = [],
        unique = {};
    for (var i in feedback.data) {
      if (typeof(unique[feedback.data[i].person_id]) === 'undefined') {
        ids.push(feedback.data[i].person_id);
      }
      unique[feedback.data[i].person_id] = 0;
    }
    return dispatch(loadPeople(recordStoresId, ids));
  }
);

export const getCommentsCounter = createAction(
  'FEEDBACK_GET_COMMENTS_COUNTER',
    ids => dispatch => dispatch(loadFeedbackCommentsCounter(recordStoresId, ids))
);

export const getStatuses = createAction(
  'FEEDBACK_GET_STATUSES',
    ids => dispatch => dispatch(loadFeedbackStatuses(recordStoresId, ids))
);

export const loadFeedbackList = createAction(
  'FEEDBACK_LIST',
  (overwriteParams = {}) => (dispatch, getState)=> {
    const state             = getState();
    const feedbackListState = state.Feedback.list.toJS();
    const currentParams     = {
      group: groupDataSelector(state),
      sort: sortingDataSelector(state).field,
      filters: filterDataSelector(state).field,
      order: feedbackListState.order
    };
    const params            = {...currentParams, ...overwriteParams};
    return dispatch => Feedback.getList(params).then(promise => {
      const feedback = promise.getData();
      let ids        = [];
      for (var i in feedback.data) {
        ids.push(feedback.data[i].id);
      }
      dispatch(getAuthors(feedback));
      dispatch(getCommentsCounter(ids));
      dispatch(getStatuses(ids));
      return feedback;
    });
  }
);

export const loadCommentsList = createAction(
  'COMMENTS_LIST',
  () => dispatch => {
    return dispatch => Feedback.commentsToReviewList().then(promise => promise.getData());
  }
);


export const feedbackToValidate = createAction(
  'FEEDBACK_TO_VALIDATE',
  () => dispatch => Feedback.toValidate().then(promise => promise.getData()));

export const commentsToReview = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW',
  () => dispatch => Feedback.commentsToReview().then(promise => promise.getData()));

export const feedbackLabels = createAction(
  'FEEDBACK_LABELS',
  () => dispatch => Feedback.getLabels().then(promise => promise.getData()));

export const feedbackTypes = createAction(
  'FEEDBACK_TYPES',
  () => dispatch => Feedback.getTypes().then(promise => promise.getData()));

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => dispatch => Feedback.getCustomCategories().then(promise => promise.getData()));

export const feedbackNew = createAction(
  'FEEDBACK_NEW_STATUS',
  () => dispatch => Feedback.getNew().then(promise => promise.getData()));


export const feedbackActiveStatus = createAction(
  'FEEDBACK_ACTIVE_STATUS',
  () => dispatch => Feedback.getActive().then(promise => promise.getData()));

export const feedbackClosedStatus = createAction(
  'FEEDBACK_CLOSED_STATUS',
  () => dispatch => Feedback.getClosed().then(promise => promise.getData()));

export const feedbackHiddenStatus = createAction(
  'FEEDBACK_HIDDEN_STATUS',
  () => dispatch => Feedback.getHidden().then(promise => promise.getData()));

export const changeGroupState = createAction(
  'FEEDBACK_CHANGE_GROUP',
    group =>  group
);

export const getFilterValues = createAction(
  'FEEDBACK_SELECT_FILTER',
  (filterName) => dispatch => Feedback.getFilterValues(filterName).then(promise => promise.getData())
);

export const resetFilterValue = createAction(
  'FEEDBACK_RESET_FILTER_VALUE'
);

export const setTableSort = createAction(
  'FEEDBACK_SET_TABLE_SORT',
  (sort, order) => dispatch => {
    dispatch(loadFeedbackList({sort: sort, order: order}));
    return {sort, order};
  });

export const toggleViewMode = createAction(
  'FEEDBACK_TOGGLE_VIEW_MODE',
    viewMode => viewMode
);

export const toggleOrder = createAction(
  'FEEDBACK_TOGGLE_ORDER',
    order => dispatch => {
    dispatch(loadFeedbackList({order: order}));
    return order;
  }
);

export const toggleSort = createAction(
  'FEEDBACK_TOGGLE_SORT',
    sort =>  dispatch => {
    dispatch(loadFeedbackList({sort: sort}));
    return sort;
  }
);

export const storeDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_STORE_DISPLAY_FIELD_TO_PERSON_SETTING',
  (displayFields) => {
    Feedback.postDisplayFieldsToPersonSetting('feedback_display_fields', displayFields).then(value => value.getData())
  });

export const getDisplayFieldsFromPersonSetting = createAction(
  'FEEDBACK_GET_DISPLAY_FIELD_FROM_PERSON_SETTING',
  () => {
    Feedback.getDisplayFieldsFromPersonSetting('feedback_display_fields').then(value => value.getData())
  });

export const toggleMassAction = createAction(
  'FEEDBACK_TOGGLE_MASS_ACTION'
);

export const toggleSelectedAction = createAction(
  'FEEDBACK_TOGGLE_SELECTED_ACTION'
);

/** @ToDo migrate to Ampliflux v2 after FilterBy block design */
export const setFilterValue = createAction(
  "FEEDBACK_SET_FILTER_VALUE",
  (trigger, filter, value) => dispatch => trigger({filter: filter, value: value})
);

export const changeDisplayFieldsStatus = createAction(
  "FEEDBACK_DISPLAY_FIELD_STATUS",
  (trigger, type, field, status, query, sort, order, filters, listViewFields, tableViewFields) => {
    trigger({type: type, field: field, status: status});
    trigger(storeDisplayFieldsToPersonSetting([{listViewFields: listViewFields, tableViewFields: tableViewFields}]));
    trigger(loadFeedbackList());
  });

export const resetFilters = createAction(
  'FEEDBACK_RESET_FILTERS',
  (trigger, filterAlias, filterName) => {
    trigger({alias: filterAlias, name: filterName, value: ''});
  });
