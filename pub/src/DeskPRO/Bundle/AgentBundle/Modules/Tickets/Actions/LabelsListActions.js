import { createAction } from "Ampliflux/actions";
import * as TicketLabels from "DeskPRO/Bundle/AgentBundle/Services/Api/TicketLabels";
import { pluck } from 'lodash';

export const loadLabels = createAction(
  "TICKETS_LOAD_TICKET_LABELS",
  (trigger) => {
    TicketLabels.loadLabels().then(
      (values) => trigger(values.getData())
    );
  }
)

export const loadLabelTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, label_name) => {
    TicketLabels.loadLabelTickets(label_name).then(
      values => trigger(values.getData())
    );
  }
)
