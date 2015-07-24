import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadLabels = createAction(
  "TICKETS_LOAD_TICKET_LABELS",
  (trigger) => {
    DpApi.sendGet('DP_API/ticket_labels').then(
      (values) => trigger(values.getData())
    );
  }
)

export const loadLabelTickets = createAction(
  "TICKETS_LOAD_TICKETS",
  (trigger, label_name) => {
    DpApi.sendGet('DP_API/ticket_labels/' + label_name + '/tickets').then(
      values => trigger(values.getData())
    );
  }
)
