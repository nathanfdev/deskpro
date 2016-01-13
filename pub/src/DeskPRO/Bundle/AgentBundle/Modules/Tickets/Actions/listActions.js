import { createAction } from 'Ampliflux';
import { listParamsSelector } from '../Selectors/list';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { setTicketsRequest } from '../RecordStores/Actions/ticketsActions';

/**
 * Used to identify requests within record stores
 * @type {string}
 */
const recordStoresId = 'tickets';

// Private -------------------------------------------------------------------------------------------------------------

const setListParams = createAction('TICKETS_LIST_SET_LIST_PARAMS');
const loadList = createAction(
  'TICKETS_LIST_LOAD_LIST',
  (params) => dispatch => {
    let url = `DP_API/ticket_filters/${params.filter}/tickets`;
    delete params.filter;
    url += '?' + compileParams(params);
    return DpApi.sendGet(url).then(promise=> {
      const res = promise.getData();
      const ids = res.data.map(item=>item.id);
      dispatch(setTicketsRequest(recordStoresId, res.data));
      return { ids: ids, pagination: res.meta.pagination };
    });
  }
);

// Public --------------------------------------------------------------------------------------------------------------

export const unload = createAction('TICKETS_LIST_UNLOAD');
export const applyListParams = createAction(
  'TICKETS_LIST_APPLY_LIST_PARAMS',
  (overwrite) => (dispatch, getState) => {
    const current = listParamsSelector(getState()).toJS();
    const params = { ...current, ...overwrite };
    dispatch(setListParams(params));

    // reload if filter param is set i.e. navigation menu item is selected
    if (params.filter) {
      dispatch(loadList(params));
    }
  }
);

// Public (control bar) ------------------------------------------------------------------------------------------------

export const setSort = createAction(
  'TICKETS_LIST_SET_SORT',
    sort => dispatch => dispatch(applyListParams({ sort }))
);
export const setOrder = createAction(
  'TICKETS_LIST_SET_ORDER',
    order => dispatch => dispatch(applyListParams({ order }))
);
export const toggleTableFieldVisibility = createAction('TICKETS_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TICKETS_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
export const setViewMode = createAction('TICKETS_LIST_SET_VIEW_MODE');
