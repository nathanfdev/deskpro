import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";
import * as TicketActions from "./FiltersActions";

export const setLoadedLabels = createAction(ActionTypes.TICKETS_LOAD_TICKET_LABELS);

export const loadLabels = () => {
  return dispatch => {
    DpApi.sendGet('DP_API/ticket_labels').then(
      (values) => {
        dispatch(setLoadedLabels(values.getData()));
      }
    );
  }
}

export const loadLabelTickets = (label_name) => {
  return (dispatch) => {
    DpApi.sendGet('DP_API/ticket_labels/' + label_name + '/tickets').then(
      values => {
        dispatch(TicketActions.setLoadedTickets(values.getData()));
      }
    );
  }
}
