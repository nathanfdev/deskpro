import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadFeedbackCommentsCounter } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCommentsActions';
import { loadFeedbackStatuses } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackStatusesActions';
import { loadFeedbackCategories } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { currentListParamsSelector } from '../Selectors/list';
import { getFeedbackForComments } from './FeedbackCommentsActions';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { setFeedbackTypesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackTypesActions';
import { setFeedbackCategoriesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const initialLoad = createAction(
  'FEEDBACK_NAV_INITIAL_LOAD',
  () => (dispatch) => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
          + '?get[customCategories]=DP_API/feedback/counts?group_by%3Dcustom_category'
          + '&get[types]=DP_API/feedback_types'
          + '&get[toValidateCount]=DP_API/feedback/counts?awaiting_validation%3D1'
          + '&get[new]=DP_API/feedback/counts?status%3Dnew%26group_by%3Dstatus_category'
          + '&get[active]=DP_API/feedback/counts?status%3Dactive%26group_by%3Dstatus_category'
          + '&get[closed]=DP_API/feedback/counts?status%3Dclosed%26group_by%3Dstatus_category'
          + '&get[commentsToReviewCount]=DP_API/feedback_comments/counts?awaiting_validation%3D1'
        ;
      DpApi.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.customCategories = payload.customCategories.nested;
        dispatch(setFeedbackCategoriesRequest(recordStoresId, payload.customCategories));
        dispatch(setFeedbackTypesRequest(recordStoresId, payload.types));
        resolve(payload);
      });
    }
  )
);

export const loadLabels = createAction(
  'FEEDBACK_LOAD_LABELS',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/feedback_labels').success(response => resolve(response.data.map(def => def.label))))
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

export const setParams = createAction('FEEDBACK_LIST_SET_CURRENT_PARAMS');

export const loadList = createAction(
  'FEEDBACK_LIST',
  (listParams) => dispatch => {
    let params = listParams;

    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }

    const isComments = params.isComments;
    delete params.isComments;

    let result;
    if (isComments) {
      result = Feedback.commentsToReviewList(params).then(promise => {
        const comments = promise.getData();
        const ids = [];
        for (var index in comments.data) {
          if (comments.data.hasOwnProperty(index)) {
            ids.push(comments.data[index].feedback_id);
          }
        }
        dispatch(getFeedbackForComments(ids));
        const people = [];
        for (const key in comments.linked.person) {
          if (comments.linked.person.hasOwnProperty(key)) {
            people.push(comments.linked.person[key]);
          }
        }
        dispatch(setPeopleRequest(recordStoresId, people));
        return comments;
      });
    } else {
      result = Feedback.getList(params).then(promise => {
        const feedback = promise.getData();
        const ids = [];
        for (var index in feedback.data) {
          if (feedback.data.hasOwnProperty(index)) {
            ids.push(feedback.data[index].id);
          }
        }
        const people = [];
        for (const key in feedback.linked.person) {
          if (feedback.linked.person.hasOwnProperty(key)) {
            people.push(feedback.linked.person[key]);
          }
        }
        dispatch(setPeopleRequest(recordStoresId, people));
        dispatch(getCommentsCounter(ids));
        dispatch(getStatuses(ids));
        dispatch(getCategories(ids));

        return feedback;
      });
    }

    return result;
  }
);

export const feedbackToValidate = createAction(
  'FEEDBACK_TO_VALIDATE',
  () => Feedback.toValidate().then(promise => promise.getData()));

export const commentsToReview = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW',
  () => Feedback.commentsToReview().then(promise => promise.getData()));

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

export const toggleViewMode = createAction(
  'FEEDBACK_TOGGLE_VIEW_MODE'
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

export const toggleMassAction = createAction('FEEDBACK_TOGGLE_MASS_ACTION');
export const toggleSelectedAction = createAction('FEEDBACK_TOGGLE_SELECTED_ACTION');
export const applyParams = createAction(
  'FEEDBACK_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    dispatch(setParams(params));
    if (params.navItem) {
      dispatch(loadList(params));
    }
  }
);
export const setSort = createAction(
  'FEEDBACK_LIST_SET_SORT',
    sort => dispatch => dispatch(applyParams({ sort }))
);
export const setOrder = createAction(
  'FEEDBACK_LIST_SET_ORDER',
    order => dispatch => dispatch(applyParams({ order }))
);
