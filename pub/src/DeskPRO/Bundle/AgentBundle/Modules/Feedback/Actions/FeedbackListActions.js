import { createAction } from 'Ampliflux';
import { pluck } from 'lodash';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadEmails } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/emailsActions';
import { loadFeedbackCommentsCounter } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCommentsActions';
import { loadFeedbackStatuses } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackStatusesActions';
import { loadFeedbackCategories } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { sortingDataSelector } from '../Selectors/list';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const getAuthors = createAction(
  'FEEDBACK_GET_AUTHORS',
  (feedback) => (dispatch) => {
    const ids = [];
    const unique = {};
    for (var index in feedback.data) {
      if (feedback.data.hasOwnProperty(index)) {
        if (typeof(unique[feedback.data[index].person_id]) === 'undefined') {
          ids.push(feedback.data[index].person_id);
        }
        unique[feedback.data[index].person_id] = 0;
      }
    }
    dispatch(loadEmails(recordStoresId, ids));
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

export const getCategories = createAction(
  'FEEDBACK_GET_CATEGORIES',
    ids => dispatch => dispatch(loadFeedbackCategories(recordStoresId, ids))
);

export const loadFeedbackList = createAction(
  'FEEDBACK_LIST',
  (overwriteParams = {}) => (dispatch, getState)=> {
    const state = getState();
    const feedbackListState = state.Feedback.list.toJS();
    const currentParams = {
      sort: sortingDataSelector(state).field,
      order: feedbackListState.order
    };
    const params = { ...currentParams, ...overwriteParams };

    return () => Feedback.getList(params).then(promise => {
      const feedback = promise.getData();
      const ids = [];
      for (var index in feedback.data) {
        if (feedback.data.hasOwnProperty(index)) {
          ids.push(feedback.data[index].id);
        }
      }
      dispatch(getAuthors(feedback));
      dispatch(getCommentsCounter(ids));
      dispatch(getStatuses(ids));
      dispatch(getCategories(ids));
      return feedback;
    });
  }
);


export const feedbackToValidate = createAction(
  'FEEDBACK_TO_VALIDATE',
  () => Feedback.toValidate().then(promise => promise.getData()));

export const commentsToReview = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW',
  () => Feedback.commentsToReview().then(promise => promise.getData()));

export const feedbackLabels = createAction(
  'FEEDBACK_LABELS',
  () => Feedback.getLabels().then(promise => promise.getData()));

export const feedbackTypes = createAction(
  'FEEDBACK_TYPES',
  () => Feedback.getTypes().then(promise => {
    const response = promise.getData();
    response.data = pluck(response.data, 'label');

    return response;
  }));

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => Feedback.getCustomCategories().then(promise => promise.getData()));

export const feedbackNew = createAction(
  'FEEDBACK_NEW_STATUS',
  () => Feedback.getNew().then(promise => promise.getData()));


export const feedbackActiveStatus = createAction(
  'FEEDBACK_ACTIVE_STATUS',
  () => Feedback.getActive().then(promise => promise.getData()));

export const feedbackClosedStatus = createAction(
  'FEEDBACK_CLOSED_STATUS',
  () => Feedback.getClosed().then(promise => promise.getData()));

export const feedbackHiddenStatus = createAction(
  'FEEDBACK_HIDDEN_STATUS',
  () => Feedback.getHidden().then(promise => promise.getData()));

export const getFilterValues = createAction(
  'FEEDBACK_SELECT_FILTER',
  (filterName) => Feedback.getFilterValues(filterName).then(promise => promise.getData())
);

export const resetFilterValue = createAction(
  'FEEDBACK_RESET_FILTER_VALUE'
);

export const setTableSort = createAction(
  'FEEDBACK_SET_TABLE_SORT',
  (sort, order) => dispatch => {
    dispatch(loadFeedbackList({ sort: sort, order: order }));
    return { sort, order };
  });

export const toggleViewMode = createAction(
  'FEEDBACK_TOGGLE_VIEW_MODE',
    viewMode => viewMode
);

export const toggleOrder = createAction(
  'FEEDBACK_TOGGLE_ORDER',
  (order) => (dispatch) => {
    dispatch(loadFeedbackList({ order: order }));
    return order;
  }
);

export const toggleSort = createAction(
  'FEEDBACK_TOGGLE_SORT',
  (sort) => (dispatch) => {
    dispatch(loadFeedbackList({ sort: sort }));
    return sort;
  }
);

export const getDisplayFieldsFromPersonSetting = createAction(
  'FEEDBACK_GET_DISPLAY_FIELD_FROM_PERSON_SETTING',
  () => PersonSetting.get('feedback_display_fields').then(value => value.getData()));

export const storeDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_STORE_DISPLAY_FIELD_TO_PERSON_SETTING',
  (displayFields) => (dispatch) =>
    PersonSetting
      .post('feedback_display_fields', displayFields)
      .then(value => {
        dispatch(getDisplayFieldsFromPersonSetting());
        return value.getData();
      })
);

export const updateDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_UPDATE_DISPLAY_FIELD_TO_PERSON_SETTING',
  (displayFields) => (dispatch) =>
    PersonSetting
      .put('feedback_display_fields', displayFields)
      .then(value => {
        dispatch(getDisplayFieldsFromPersonSetting());
        return value.getData();
      })
);

export const toggleMassAction = createAction(
  'FEEDBACK_TOGGLE_MASS_ACTION'
);

export const toggleSelectedAction = createAction(
  'FEEDBACK_TOGGLE_SELECTED_ACTION'
);

/** @ToDo migrate to Ampliflux v2 after FilterBy block design */
export const setFilterValue = createAction(
  'FEEDBACK_SET_FILTER_VALUE',
  (trigger, filter, value) => () => trigger({ filter: filter, value: value })
);

export const resetFilters = createAction(
  'FEEDBACK_RESET_FILTERS',
  (trigger, filterAlias, filterName) => {
    trigger({ alias: filterAlias, name: filterName, value: '' });
  });
