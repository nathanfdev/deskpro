import { createAction } from 'Ampliflux';
import * as People from 'DeskPRO/Bundle/AgentBundle/Services/Api/People';
import * as Organizations from 'DeskPRO/Bundle/AgentBundle/Services/Api/Organizations';
import { currentListParamsSelector } from '../Selectors/list';
import { setOrganizationsRequest } from '../RecordStores/Actions/organizationsActions';
import { setUserGroupsRequest } from '../RecordStores/Actions/userGroupsActions';
import { setLanguagesRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/RecordStores/Actions/languagesActions';

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

export const loadPeople = createAction(
  'CRM_LIST_LOAD_DATA',
  (params) => dispatch => People.loadPeople(params).then(promise => {
    const data = promise.getData();
    dispatch(setOrganizationsRequest(recordStoresId, prepareLinkedData(data.linked.organization)));
    dispatch(setUserGroupsRequest(recordStoresId, prepareLinkedData(data.linked.usergroup)));
    dispatch(setLanguagesRequest(recordStoresId, prepareLinkedData(data.linked.language)));
    return { content: params.content, data: data };
  })
);

export const loadOrganizations = createAction(
  'CRM_LIST_LOAD_DATA',
  (params) => Organizations.load(params).then(promise => {
    const data = promise.getData();
    return { content: params.content, data: data };
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
    dispatch(setParams(params));
    if (params.content && !delayReload) {
      dispatch(load(params));
    }
  }
);


