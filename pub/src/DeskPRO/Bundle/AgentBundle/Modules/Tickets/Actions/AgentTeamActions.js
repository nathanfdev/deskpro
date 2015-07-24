import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadAgentTeam = createAction(
  "TICKETS_AGENT_TEAM_LOADED",
  (trigger, agent_team_id) => {
    DpApi.sendGet('DP_API/agent_teams/' + agent_team_id).then(
      (values) => {
        trigger(values.getData());
      }
    );
  }
);
