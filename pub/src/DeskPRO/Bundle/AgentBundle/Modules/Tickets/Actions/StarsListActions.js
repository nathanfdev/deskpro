import { createAction } from "Ampliflux/actions";
import Stars from "DeskPRO/Bundle/AgentBundle/Services/Api/Stars";

export const loadStarCounts = createAction(
  "TICKETS_LOAD_TICKET_STAR_COUNTS",
  (trigger) => Stars.loadAllStarCounts.then(
      (values) => trigger(values.getData())
  )
)

export const loadStarTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, star_name) => Stars.loadTicketsForStar(star_name).then(
      values => trigger(values.getData())
  )
)
