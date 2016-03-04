import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { currentListParamsSelector } from '../Selectors/list';
import { setCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { toggleMassAction } from '../../Application/Actions/massActions';

const recordStoresId = 'crm';

const prepareLinkedData = (linked) => {
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const setParams = createAction('CRM_LIST_SET_CURRENT_PARAMS');

const include = 'organization,usergroup,language';
export const loadPeople = createAction(
  'CRM_LIST_LOAD_DATA',
  (params) => dispatch => repository('Person').search(params, include).then(promise => {
    const res = promise.getData();
    dispatch(setCollection('Organization', recordStoresId, prepareLinkedData(res.linked.organization)));
    dispatch(setCollection('UserGroup', recordStoresId, prepareLinkedData(res.linked.usergroup)));
    dispatch(setCollection('Language', recordStoresId, prepareLinkedData(res.linked.language)));
    dispatch(setCollection('Person', recordStoresId, res.data));
    const ids = res.data.map(item=>item.id);
    return { ids: ids, pagination: res.meta.pagination };
  })
);

export const loadOrganizations = createAction(
  'CRM_LIST_LOAD_DATA',
  (params) => dispatch => repository('Organization').search(params).then(promise => {
    const res = promise.getData();
    dispatch(setCollection('Organization', recordStoresId, res.data));
    const ids = res.data.map(item=>item.id);
    return { ids: ids, pagination: res.meta.pagination };
  })
);

export const load = createAction(
  'CRM_LIST_LOAD_DATA',
  (listParams) => dispatch => {
    let params = listParams;
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }
    dispatch(toggleMassAction());
    if (params.content === 'organizations') {
      dispatch(loadOrganizations(params));
    } else {
      dispatch(loadPeople(params));
    }
  }
);

export const applyParams = createAction(
  'CRM_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    const { delayReload } = params;
    if (!overwrite.hasOwnProperty('page') && current.hasOwnProperty('page')) {
      delete params.page;
    }
    delete params.delayReload;
    if (!overwrite.hasOwnProperty('navItem')) {
      delete params.navItem;
    }
    dispatch(setParams(params));
    if (params.content && !delayReload) {
      dispatch(load(params));
    }
  }
);

export const setOrderBy = createAction(
  'CRM_LIST_SET_ORDER_BY',
    orderBy => dispatch => dispatch(applyParams({ 'order_by': orderBy, delayReload: true }))
);
export const setOrderDir = createAction(
  'CRM_LIST_SET_ORDER_DIR',
    orderDir => dispatch => dispatch(applyParams({ 'order_dir': orderDir, delayReload: true }))
);
