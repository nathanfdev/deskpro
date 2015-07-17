import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const changeTab = createAction(ActionTypes.TICKETS_CHANGE_TAB);

export const setLoadedFilterSets = createAction(ActionTypes.LOAD_FILTER_SETS);

export const selectTicketFilter = createAction(ActionTypes.TICKETS_SELECT_FILTER);

export const setLoadedTickets = createAction(ActionTypes.LOAD_TICKETS);

export const loadFilterSets = () => {
  return dispatch => {
    let promises = [];
    
    promises.push(DpApi.sendGet('DP_API/ticket_filter_sets'));
    
    Promise.all(promises).then(
      values => {
        dispatch(setLoadedFilterSets(values[0].getData()));
      }
    );
  }
}

export const loadFilterTickets = (filter_id) => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_filters/' + filter_id + '/tickets').then(
      values => {
        dispatch(setLoadedTickets(values.getData()));
      }
    );
  }
}


