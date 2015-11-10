import { createAction } from 'Ampliflux';
import { listParamsSelector } from '../Selectors/list';
import DpApi from 'DeskPRO/Bundle/AgentBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';

// ---------------------------------------------------------------------------------------------------------------------
// Private
// ---------------------------------------------------------------------------------------------------------------------

const setListParams = createAction('TICKETS_LIST_SET_LIST_PARAMS');

const loadList = createAction(
  'TICKETS_LIST_LOAD_LIST',
  params => new Promise(resolve => {
    let url = `DP_API/ticket_filters/${params.filter}/tickets`;
    delete params.filter;
    url += '?' + compileParams(params);

    return DpApi.sendGet(url).success(response => resolve(response.data));
  })
);

// ---------------------------------------------------------------------------------------------------------------------
// Public
// ---------------------------------------------------------------------------------------------------------------------

export const applyListParams = createAction(
  'TICKETS_LIST_APPLY_LIST_PARAMS',
  overwrite => (dispatch, getState) => {
    const current = listParamsSelector(getState()).toJS();
    const params = {...current, ...overwrite};
    dispatch(setListParams(params));

    if (params.filter) {
      dispatch(loadList(params));
    }
  }
);

export const toggleAll = createAction('TICKETS_LIST_TOGGLE_ALL_ACTION');
export const toggleSelected = createAction('TICKETS_LIST_TOGGLE_SELECTED');
export const setSort = createAction(
  'TICKETS_LIST_SET_SORT',
  sort => dispatch => dispatch(applyListParams({sort}))
);
export const setOrder = createAction(
  'TICKETS_LIST_SET_ORDER',
  order => dispatch => dispatch(applyListParams({order}))
);
export const toggleTableFieldVisibility = createAction('TICKETS_LIST_TOGGLE_TABLE_FIELD_VISIBILITY');
export const toggleCardFieldVisibility = createAction('TICKETS_LIST_TOGGLE_CARD_FIELD_VISIBILITY');
export const setViewMode = createAction('TICKETS_LIST_SET_VIEW_MODE');
