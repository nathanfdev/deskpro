import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const agentTeamLoaded = createAction(ActionTypes.TICKETS_AGENT_TEAM_LOADED);

export function loadAgentTeam(agent_team_id) {
  return (dispatch) => {
    DpApi.sendGet('DP_API/agent_teams/' + agent_team_id).then(
      values => {
        dispatch(agentTeamLoaded(values.getData()));
      }
    );
  }
}
