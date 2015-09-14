import { createAction } from "Ampliflux";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const hideSidebarHover = createAction("TICKETS_SIDEBAR_HOVER_HIDE");

export const showFilterGroupingOptions = createAction(
  "TICKETS_SIDEBAR_HOVER_FILTER_GROUPING_OPTIONS",
  (trigger, payload = null) => {
    trigger(payload);
  }
)
