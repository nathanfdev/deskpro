import { createAction } from 'Ampliflux';
import { pluck } from 'lodash';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import { loadPeople } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadEmails } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/emailsActions';
import { loadFeedbackCommentsCounter } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCommentsActions';
import { loadFeedbackStatuses } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackStatusesActions';
import { loadFeedbackCategories } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { currentListParamsSelector } from '../Selectors/list';
import Moment from 'moment';

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

export const setCurrentListParams = createAction(
  'FEEDBACK_LIST_SET_CURRENT_PARAMS'
);

export const loadFeedbackList = createAction(
    'FEEDBACK_LIST',
    (overwriteParams = {}) => (dispatch, getState)=> {
      const currentParams = currentListParamsSelector(getState()).toJS();

      let params = { ...currentParams, ...overwriteParams };
      params.isComments = false;
      delete params.isComments;
      dispatch(setCurrentListParams(params));
      const {navItem} = params;
      if (navItem) {
        delete params.navItem;
        params = { ...params, ...navItem };
      }
      const {filters} = params;
      if (filters) {
        delete params.filters;
        for (var property in filters) {
          if (filters.hasOwnProperty(property)) {
            if (property === 'date_created') {
              for (var dateProperty in filters[property]) {
                if (filters[property].hasOwnProperty(dateProperty) && filters[property][dateProperty]) {
                  params[dateProperty] = Moment(filters[property][dateProperty]).format('YYYY-MM-DD HH:mm:ss');
                }
              }
            } else {
              params[property] = filters[property];
            }
          }
        }
      }

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
  )
  ;

export const feedbackToValidate = createAction(
  'FEEDBACK_TO_VALIDATE',
  () => Feedback.toValidate().then(promise => promise.getData()));

export const commentsToReview = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW',
  () => Feedback.commentsToReview().then(promise => promise.getData()));

export const feedbackLabels = createAction(
  'FEEDBACK_LABELS',
  () => Feedback.getLabels().then(promise => {
    const response = promise.getData();
    response.data = pluck(response.data, 'label');

    return response;
  }));

export const feedbackTypes = createAction(
  'FEEDBACK_TYPES',
  () => Feedback.getTypes().then(promise => promise.getData())
);

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => Feedback.getCustomCategories().then(promise => promise.getData())
);

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

export const setTableSort = createAction(
  'FEEDBACK_SET_TABLE_SORT',
  (sort, order) => dispatch => {
    dispatch(loadFeedbackList({ sort: sort, order: order }));
    return { sort, order };
  });

export const toggleViewMode = createAction(
  'FEEDBACK_TOGGLE_VIEW_MODE'
);

export const setOrder = createAction(
  'FEEDBACK_SET_ORDER',
    order => dispatch => dispatch(loadFeedbackList({ order: order }))
);

export const setSort = createAction(
  'FEEDBACK_SET_SORT',
    sort => dispatch => dispatch(loadFeedbackList({ sort: sort }))
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

export const setFilterValue = createAction(
  'FEEDBACK_SET_FILTER_VALUE',
    update => update
);