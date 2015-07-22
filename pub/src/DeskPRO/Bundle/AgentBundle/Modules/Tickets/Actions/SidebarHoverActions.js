import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";
import * as TicketActions from "./FiltersActions";

export const hideSidebarHover = createAction(ActionTypes.TICKETS_SIDEBAR_HOVER_HIDE);

export function showFilterGroupingOptions(payload = null) {
  return {
    type: ActionTypes.TICKETS_SIDEBAR_HOVER_FILTER_GROUPING_OPTIONS,
    payload: payload
  };
}
