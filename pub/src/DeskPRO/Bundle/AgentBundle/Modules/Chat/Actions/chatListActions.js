import { createAction } from 'Ampliflux';
import { load as loadChats } from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';
import { sortingDataSelector, currentListParamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Selectors/list';

export const load = createAction(
  'CHAT_LIST_LOAD_DATA',
  (filters = {}) =>
    (dispatch, getState) => {
      const sorting = sortingDataSelector(getState());
      const params = {...sorting, ...filters};
      dispatch(updateCurrentListParams(params));

      return loadChats(params).then(promise => promise.getData().data);
    }
);

export const reLoad = createAction(
  'CHAT_LIST_RELOAD_DATA',
  (overwriteParams = {}) =>
    (dispatch, getState) => {
      const currentParams = currentListParamsSelector(getState());
      const params = {...currentParams, ...overwriteParams};
      dispatch(updateCurrentListParams(params));

      return loadChats(params).then(promise => promise.getData().data);
    }
);

export const updateCurrentListParams = createAction(
  'CHAT_LIST_UPDATE_CURRENT_LIST_PARAMS',
  params => params
);

export const changeSort = createAction(
  'CHAT_LIST_CHANGE_SORT',
  sort => dispatch => {
    dispatch(reLoad({sort}));

    return sort;
  }
);
