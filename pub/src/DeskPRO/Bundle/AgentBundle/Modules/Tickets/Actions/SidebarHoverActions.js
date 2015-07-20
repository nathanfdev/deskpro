import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";
import * as TicketActions from "./TicketsListActions";

export const showFilterGroupingOptions = createAction(ActionTypes.TICKETS_SIDEBAR_HOVER_FILTER_GROUPING_OPTIONS);
