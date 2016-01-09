import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import * as Feedback from 'DeskPRO/Bundle/AgentBundle/Services/Api/Feedback';
import * as PersonSetting from 'DeskPRO/Bundle/AgentBundle/Services/Api/PersonSetting';
import { loadFeedbackCommentsList } from './FeedbackCommentsActions';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { loadFeedbackCategories } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/RecordStores/Actions/feedbackCategoriesActions';
import { currentListParamsSelector, visibleFieldsSelector } from '../Selectors/list';
import { setFeedbackStatusCategoriesRequest } from '../RecordStores/Actions/feedbackStatusCategoriesActions';
import { loadFeedbackCommentsCounter } from '../RecordStores/Actions/feedbackCommentsActions';
import { setFeedbackRequest } from '../RecordStores/Actions/feedbackActions';
import { toggleMassAction } from './FeedbackMassActions';
import { feedbackToValidateCounter } from './feedbackNavActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'feedback';

const prepareLinkedData = (linked) => {
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const loadLabels = createAction(
  'FEEDBACK_LOAD_LABELS',
  () => new Promise(resolve =>
    DpApi.sendGet('DP_API/feedback_labels').success(response => resolve(response.data.map(def => def.label))))
);

export const getCategories = createAction(
  'FEEDBACK_GET_CATEGORIES',
    ids => dispatch => dispatch(loadFeedbackCategories(recordStoresId, ids))
);

export const setParams = createAction('FEEDBACK_LIST_SET_CURRENT_PARAMS');

export const getCommentsCounter = createAction(
  'FEEDBACK_GET_COMMENTS_COUNTER',
    ids => dispatch => dispatch(loadFeedbackCommentsCounter(recordStoresId, ids))
);

export const setDisplayFields = createAction(
  'FEEDBACK_SET_DISPLAY_FIELDS',
    payload => payload
);

export const loadFeedbackList = createAction(
  'FEEDBACK_LIST_OF_FEEDBACK',
    params => (dispatch) => Feedback.getList(params).then(promise => {
      const res = promise.getData();
      const ids = res.data.map(item=>item.id);

      dispatch(setFeedbackRequest(recordStoresId, res.data));
      dispatch(setPeopleRequest(recordStoresId, prepareLinkedData(res.linked.person)));
      dispatch(setFeedbackStatusCategoriesRequest(recordStoresId, prepareLinkedData(res.linked.feedback_status_category)));
      dispatch(getCommentsCounter(ids));

      return { ids: ids, pagination: res.meta.pagination };
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
    dispatch(toggleMassAction());
    return params;
  }
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
    const displayFields = visibleFieldsSelector(getState());
    PersonSetting.post('feedback_display_fields', displayFields);
    dispatch(setViewFieldsSettingStoredFlag(true));
    return displayFields;
  }
);

export const updateDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_UPDATE_DISPLAY_FIELD_TO_PERSON_SETTING',
  () => (dispatch, getState) => {
    const displayFields = visibleFieldsSelector(getState());
    PersonSetting.put('feedback_display_fields', displayFields);
    return displayFields;
  }
);

export const applyParams = createAction(
  'FEEDBACK_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    if (overwrite.hasOwnProperty('navItem')) {
      const typesOfStatus = ['status', 'status_category', 'hidden_status'];
      typesOfStatus.forEach((type)=> {
        if (overwrite.navItem.hasOwnProperty(type)) {
          typesOfStatus.splice(typesOfStatus.indexOf(type), 1);
          typesOfStatus.forEach((item) => delete current[item]);
        }
      });
    }
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


export const deleteFeedback = createAction(
  'FEEDBACK_DELETE',
  (ids) => dispatch => {
    Feedback.deleteFeedback(ids).then(()=> {
      dispatch(feedbackToValidateCounter());
      dispatch(applyParams({ isComments: false }));
    });
    return ids;
  }
);

export const approveFeedback = createAction(
  'FEEDBACK_APPROVE',
  (ids) => dispatch => {
    Feedback.approveFeedback(ids).then(()=> {
      dispatch(feedbackToValidateCounter());
      dispatch(applyParams({ isComments: false }));
    });
    return ids;
  }
);
