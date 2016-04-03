import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { loadFeedbackCommentsList } from './FeedbackCommentsActions';
import { currentListParamsSelector, visibleFieldsSelector } from '../Selectors/list';
import { toggleMassAction } from '../../Application/Actions/massActions';
import { loadBatch, setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

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
    api.sendGet('DP_API/feedback_labels').success(response => resolve(response.data.map(def => def.label))))
);

export const getCategories = createAction(
  'FEEDBACK_GET_CATEGORIES',
    ids => dispatch => dispatch(loadBatch('FeedbackCategory', ids, recordStoresId))
);

export const setParams = createAction('FEEDBACK_LIST_SET_CURRENT_PARAMS');
export const loadIndicator = createAction('FEEDBACK_LIST_LOAD_INDICATOR');

export const getCommentsCounter = createAction(
  'FEEDBACK_GET_COMMENTS_COUNTER',
    ids => dispatch => dispatch(loadBatch('FeedbackCommentCounter', ids, recordStoresId))
);

export const setDisplayFields = createAction(
  'FEEDBACK_SET_DISPLAY_FIELDS',
    payload => payload
);

export const loadFeedbackList = createAction(
  'FEEDBACK_LIST_OF_FEEDBACK',
    params => (dispatch) => repository('Feedback').search(params).then(promise => {
      const res = promise.getData();
      const ids = res.data.map(item=>item.id);

      dispatch(setCollection('Feedback', recordStoresId, res.data));
      dispatch(setCollection('Person', recordStoresId, prepareLinkedData(res.linked.person)));
      dispatch(setCollection('FeedbackStatusCategory', recordStoresId, prepareLinkedData(res.linked.feedback_status_category)));
      dispatch(getCommentsCounter(ids));

      return { ids: ids, pagination: res.meta.pagination };
    }
  ));

export const getDisplayFieldsFromPersonSetting = createAction(
  'FEEDBACK_GET_DISPLAY_FIELD_FROM_PERSON_SETTING',
  () => repository('PersonSetting').load('feedback_display_fields').then(value => value.getData())
);

export const setViewFieldsSettingStoredFlag = createAction(
  'FEEDBACK_SET_VIEW_FIELDS_SETTING_STORED_FLAG',
    payload => payload
);

export const storeDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_STORE_DISPLAY_FIELD_TO_PERSON_SETTING',
  () => (dispatch, getState) => {
    const displayFields = visibleFieldsSelector(getState());
    repository('PersonSetting').create({ name: 'feedback_display_fields', value: displayFields });
    dispatch(setViewFieldsSettingStoredFlag(true));
    return displayFields;
  }
);

export const updateDisplayFieldsToPersonSetting = createAction(
  'FEEDBACK_UPDATE_DISPLAY_FIELD_TO_PERSON_SETTING',
  () => (dispatch, getState) => {
    const displayFields = visibleFieldsSelector(getState());
    repository('PersonSetting').update({ name: 'feedback_display_fields', value: displayFields });
    return displayFields;
  }
);
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

export const applyParams = createAction(
  'FEEDBACK_APPLY_LIST_PARAMS',
  (params = {}) => (dispatch, getState) => {
    if (params.hasOwnProperty('navItem')) {
      const typesOfStatus = ['status', 'status_category', 'hidden_status'];
      typesOfStatus.forEach((type)=> {
        if (params.navItem.hasOwnProperty(type)) {
          typesOfStatus.splice(typesOfStatus.indexOf(type), 1);
          typesOfStatus.forEach((item) => delete params[item]);
        }
      });
    }
    const current = currentListParamsSelector(getState()).toJS();
    const newParams = { ...current, ...params };
    dispatch(setParams(newParams));
    dispatch(loadList(newParams));
  }
);

export const setOrderBy = createAction(
  'FEEDBACK_LIST_SET_ORDER_BY',
    orderBy => dispatch => dispatch(applyParams({ 'order_by': orderBy }))
);
export const setOrderDir = createAction(
  'FEEDBACK_LIST_SET_ORDER_DIR',
    orderDir => dispatch => dispatch(applyParams({ 'order_dir': orderDir }))
);

export const toggleTableFieldVisibility = createAction('FEEDBACK_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('FEEDBACK_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
