import { createAction } from "Ampliflux";
import * as AgentTeams from "DeskPRO/Bundle/AgentBundle/Services/Api/AgentTeams";

export const loadAgentTeam = createAction(
  "TICKETS_AGENT_TEAM_LOADED",
  (trigger, agent_team_id) => {
      AgentTeams.loadAgentTeam(agent_team_id).then(
          (values) => {
            trigger(values.getData());
          }
      );
  }
);
