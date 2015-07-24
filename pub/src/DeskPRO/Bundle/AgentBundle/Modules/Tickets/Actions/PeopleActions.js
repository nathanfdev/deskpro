import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadPeople = createAction(
  "TICKETS_PEOPLE_LOADED",
  (trigger, people_id) => {
    DpApi.sendGet('DP_API/people/' + people_id).then(
      values => trigger(values.getData())
    );
  }
)
