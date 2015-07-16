import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const changeTab = createAction(ActionTypes.TICKETS_CHANGE_TAB);

export const setLoadedFilterSets = createAction(ActionTypes.LOAD_FILTER_SETS);

export function loadTicketSets() {
  return dispatch => {
    let promises = [];
    
    DpApi.sendGet('DP_API/ticket_filter_sets').then(
      (values) => {
        dispatch(setLoadedFilterSets(values[0].getData()));
      }
    );
  }
}
