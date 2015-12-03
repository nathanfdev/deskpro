import { createAction } from 'Ampliflux';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadFeedbackCommentsCounter } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCommentsActions';
import { loadFeedbackCategories } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { currentListParamsSelector, currentViewFieldsParamsSelector } from '../Selectors/list';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { flattenBatchResponses } from 'DeskPRO/Component/Util/Api';
import { setFeedbackRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackActions';
import { setFeedbackTypesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackTypesActions';
import { setFeedbackCategoriesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { setFeedbackStatusCategoriesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackStatusCategoriesActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

export const setDisplayFields = createAction(
  'FEEDBACK_SET_DISPLAY_FIELDS',
    payload => payload
);

export const initialLoad = createAction(
  'FEEDBACK_NAV_INITIAL_LOAD',
  () => (dispatch) => new Promise(
    (resolve) => {
      const batch = 'DP_API/batch'
          + '?get[customCategories]=DP_API/feedback/counts?group_by%3Dcustom_category'
          + '&get[types]=DP_API/feedback_types'
          + '&get[labels]=DP_API/feedback_labels'
          + '&get[toValidateCount]=DP_API/feedback/counts?awaiting_validation%3D1'
          + '&get[new]=DP_API/feedback/counts?status%3Dnew'
          + '&get[active]=DP_API/feedback/counts?status%3Dactive%26group_by%3Dstatus_category'
          + '&get[closed]=DP_API/feedback/counts?status%3Dclosed%26group_by%3Dstatus_category'
          + '&get[hidden]=DP_API/feedback/counts?status%3Dhidden%26group_by%3Dhidden_status'
          + '&get[commentsToReviewCount]=DP_API/feedback_comments/counts?awaiting_validation%3D1'
          + '&get[viewFields]=DP_API/person_setting/feedback_display_fields'
        ;
      DpApi.sendGet(batch).success(({responses}) => {
        const payload = flattenBatchResponses(responses);
        payload.customCategories = payload.customCategories.nested;
        payload.statuses = { new: payload.new, active: payload.active, closed: payload.closed, hidden: payload.hidden };
        if (payload.viewFields && payload.viewFields.hasOwnProperty('value')) {
          dispatch(setDisplayFields({
            cardVisibleFields: payload.viewFields.value.cardVisibleFields,
            tableVisibleFields: payload.viewFields.value.tableVisibleFields,
            viewFieldsSettingsFromDb: true
          }));
        } else {
          const defaultCardViewFields = ['id', 'title', 'person', 'status', 'date_created', 'labels'];
          const defaultTableViewFields = ['id', 'title', 'person', 'status', 'date_created', 'labels'];
          dispatch(setDisplayFields({
            cardVisibleFields: defaultCardViewFields,
            tableVisibleFields: defaultTableViewFields,
            viewFieldsSettingsFromDb: false
          }));
        }
        dispatch(setFeedbackTypesRequest(recordStoresId, payload.types));
        delete payload.new;
        delete payload.active;
        delete payload.closed;
        delete payload.hidden;
        delete payload.viewFields;
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

export const getCategories = createAction(
  'FEEDBACK_GET_CATEGORIES',
    ids => dispatch => dispatch(loadFeedbackCategories(recordStoresId, ids))
);

export const setParams = createAction('FEEDBACK_LIST_SET_CURRENT_PARAMS');

export const loadFeedbackList = createAction(
  'FEEDBACK_LIST_OF_FEEDBACK',
    params => (dispatch) => Feedback.getList(params).then(promise => {
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

      const statusCategories = [];
      for (const key in feedback.linked.feedback_status_category) {
        if (feedback.linked.feedback_status_category.hasOwnProperty(key)) {
          statusCategories.push(feedback.linked.feedback_status_category[key]);
        }
      }

      const customCategories = [];
      for (const key in feedback.linked.custom_data_feedback) {
        if (feedback.linked.custom_data_feedback.hasOwnProperty(key)) {
          customCategories.push(feedback.linked.custom_data_feedback[key]);
        }
      }

      dispatch(setPeopleRequest(recordStoresId, people));
      dispatch(setFeedbackStatusCategoriesRequest(recordStoresId, statusCategories));
      dispatch(setFeedbackCategoriesRequest(recordStoresId, customCategories));
      dispatch(getCommentsCounter(ids));

      return feedback;
    }
  ));
export const loadFeedbackCommentsList = createAction(
  'FEEDBACK_LIST_OF_COMMENTS',
    params => (dispatch) => Feedback.commentsToReviewList(params).then(promise => {
      const comments = promise.getData();
      const feedback = [];
      for (var index in comments.linked.feedback) {
        if (comments.linked.feedback.hasOwnProperty(index)) {
          feedback.push(comments.linked.feedback[index]);
        }
      }
      const people = [];
      for (const key in comments.linked.person) {
        if (comments.linked.person.hasOwnProperty(key)) {
          people.push(comments.linked.person[key]);
        }
      }
      dispatch(setFeedbackRequest(recordStoresId, feedback));
      dispatch(setPeopleRequest(recordStoresId, people));
      return comments;
    }
  ));
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

    if (isComments) {
      dispatch(loadFeedbackCommentsList(params));
    } else {
      dispatch(loadFeedbackList(params));
    }

    return params;
  }
);

export const commentsToReview = createAction(
  'FEEDBACK_COMMENTS_TO_REVIEW',
  () => Feedback.commentsToReview().then(promise => promise.getData()));

export const feedbackCustomCategories = createAction(
  'FEEDBACK_CUSTOM_CATEGORIES',
  () => Feedback.getCustomCategories().then(promise => promise.getData())
);

export const toggleViewMode = createAction(
  'FEEDBACK_TOGGLE_VIEW_MODE'
);

export const getDisplayFieldsFromPersonSetting = createAction(
  'FEEDBACK_GET_DISPLAY_FIELD_FROM_PERSON_SETTING',
  () => PersonSetting.get('feedback_display_fields').then(value => value.getData())
);

export const setViewFieldsSettingStoredFlag = createAction(
  'FEEDBACK_SET_VIEW_FIELDS_SETTING_STORED_FLAG',
    payload => payload
);

export const storeDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_STORE_DISPLAY_FIELD_TO_PERSON_SETTING',
  () => (dispatch, getState) => {
    const displayFields = currentViewFieldsParamsSelector(getState());
    PersonSetting.post('feedback_display_fields', displayFields);
    dispatch(setViewFieldsSettingStoredFlag(true));
    return displayFields;
  }
);

export const updateDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_UPDATE_DISPLAY_FIELD_TO_PERSON_SETTING',
  () => (dispatch, getState) => {
    const displayFields = currentViewFieldsParamsSelector(getState());
    PersonSetting.put('feedback_display_fields', displayFields);
    return displayFields;
  }
);

export const applyParams = createAction(
  'FEEDBACK_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    const { delayReload } = params;
    if (!overwrite.hasOwnProperty('page') && current.hasOwnProperty('page')) {
      delete params.page;
    }
    delete params.delayReload;
    dispatch(setParams(params));
    if (params.navItem && !delayReload) {
      dispatch(loadList(params));
    }
  }
);
export const setSort = createAction(
  'FEEDBACK_LIST_SET_SORT',
    sort => dispatch => dispatch(applyParams({ sort, delayReload: true }))
);
export const setOrder = createAction(
  'FEEDBACK_LIST_SET_ORDER',
    order => dispatch => dispatch(applyParams({ order, delayReload: true }))
);

export const toggleTableFieldVisibility = createAction('FEEDBACK_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('FEEDBACK_LIST_TOGGLE_CARD_FIELD_VISIBILITY');

export const toggleMassAction = createAction('FEEDBACK_TOGGLE_MASS_ACTION');
export const toggleSelectedAction = createAction('FEEDBACK_TOGGLE_SELECTED_ACTION');

export const setMassActionsParams = createAction(
  'FEEDBACK_SET_MASS_ACTIONS_PARAMS',
    params => params
);
export const resetMassActionsParams = createAction('FEEDBACK_RESET_MASS_ACTIONS_PARAMS');

export const massAction = createAction(
  'FEEDBACK_MASS_ACTION',
  (params) => (dispatch) =>
    Feedback.massAction(params)
      .then(promise => {
        dispatch(resetMassActionsParams());
        dispatch(applyParams());
        return promise.getData();
      }
    ));
