import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";
import * as TicketActions from "./TicketsListActions";

export const setLoadedStarCounts = createAction(ActionTypes.TICKETS_LOAD_TICKET_STAR_COUNTS);

export const loadStarCounts = () => {
  return dispatch => {
    DpApi.sendGet('DP_API/ticket_stars/all/counts').then(
      (values) => {
        dispatch(setLoadedStarCounts(values.getData()));
      }
    );
  }
}

export const loadStarTickets = (star_name) => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_stars/' + star_name + '/tickets').then(
      values => {
        dispatch(TicketActions.setLoadedTickets(values.getData()));
      }
    );
  }
}
