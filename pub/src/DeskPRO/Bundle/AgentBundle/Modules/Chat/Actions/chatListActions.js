import { createAction } from 'Ampliflux';
import { load as loadChats } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import { currentListParamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors/list';
import { setChatsRequest } from '../RecordStores/Actions/chatsActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'chats';

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
    return loadChats(params).then(promise => {
      const res = promise.getData();
      const ids = res.data.map(item=>item.id);
      dispatch(setChatsRequest(recordStoresId, res.data));

      return { ids: ids, pagination: res.meta.pagination };
    });
  }
);

export const reLoad = createAction(
  'CHAT_LIST_RELOAD_DATA',
  (overwriteParams = {}) =>
    (dispatch, getState) => {
      const currentParams = currentListParamsSelector(getState());
      const params = { ...currentParams, ...overwriteParams };
      dispatch(updateCurrentListParams(params));

      return loadChats(params).then(promise => promise.getData().data);
    }
);

export const changeSort = createAction(
  'CHAT_LIST_CHANGE_SORT',
  (sort) => dispatch => {
    dispatch(reLoad({ sort }));
    return sort;
  }
);

export const toggleOrder = createAction(
  'CHAT_LIST_TOGGLE_ORDER',
  (order) => dispatch => {
    dispatch(reLoad({ order: order }));
    return order;
  }
);

export const toggleViewMode = createAction(
  'CHAT_LIST_TOGGLE_VIEW_MODE',
    viewMode => viewMode
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
