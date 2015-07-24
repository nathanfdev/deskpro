import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadStarCounts = createAction(
  "TICKETS_LOAD_TICKET_STAR_COUNTS",
  (trigger) => {
    DpApi.sendGet('DP_API/ticket_stars/all/counts').then(
      (values) => trigger(values.getData())
    );
  }
)

export const loadStarTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, star_name) => {
    DpApi.sendGet('DP_API/ticket_stars/' + star_name + '/tickets').then(
      values => trigger(values.getData())
    );
  }
)
