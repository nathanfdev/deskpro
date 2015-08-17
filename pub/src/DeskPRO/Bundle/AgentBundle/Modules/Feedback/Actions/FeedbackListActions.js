import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import * as Feedback from "DeskPRO/Bundle/AgentBundle/Services/Api/Feedback";

export const bogusAction = createAction(
  "FEEDBACK_BOGUS",
  (trigger) => {
    setTimeout(function() {
      trigger(true);
    }, 500);
  }
);
