import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';
import { currentListParamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors/list';
import { setPeopleRequest } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/peopleActions';
import { setCollection } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';
import { toggleMassAction } from '../../Application/Actions/massActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'chats';

const prepareLinkedData = (linked) => {
  const result = [];
  for (const key in linked) {
    if (linked.hasOwnProperty(key)) {
      result.push(linked[key]);
    }
  }
  return result;
};

export const updateCurrentListParams = createAction(
  'CHAT_LIST_UPDATE_CURRENT_LIST_PARAMS',
    params => params
);

export const load = createAction(
  'CHAT_LIST_LOAD_DATA',
  (listParams) => dispatch => {
    let params = listParams;
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }
    return repository('Chat').search(params, 'person,agent,department').then(response => {
      const res = response.getData();
      dispatch(setCollection('Chat', recordStoresId, res.data));
      dispatch(setPeopleRequest(recordStoresId, prepareLinkedData(res.linked.person)));
      dispatch(setCollection('Department', recordStoresId, prepareLinkedData(res.linked.department)));
      dispatch(toggleMassAction());

      return {pagination: res.meta.pagination};
    });
  }
);

export const applyParams = createAction(
  'CHAT_APPLY_LIST_PARAMS',
  (overwrite = {}) => (dispatch, getState) => {
    const current = currentListParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    const { delayReload } = params;
    if (!overwrite.hasOwnProperty('page') && current.hasOwnProperty('page')) {
      delete params.page;
    }
    delete params.delayReload;
    dispatch(updateCurrentListParams(params));
    if (params.navItem && !delayReload) {
      dispatch(load(params));
    }
  }
);


export const changeSort = createAction(
  'CHAT_LIST_CHANGE_SORT',
    sort => dispatch => dispatch(applyParams({ sort, delayReload: true }))
);

export const toggleOrder = createAction(
  'CHAT_LIST_TOGGLE_ORDER',
    order => dispatch => dispatch(applyParams({ order, delayReload: true }))
);
