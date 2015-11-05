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
  params => (dispatch) => {
    dispatch(setListParams(params));
    dispatch(loadList(params));
  }
);

export const toggleMassAction = createAction('TICKETS_LIST_TOGGLE_MASS_ACTION');
export const toggleSelected = createAction('TICKETS_LIST_TOGGLE_SELECTED');