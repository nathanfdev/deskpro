import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const agentLoaded = createAction(ActionTypes.TICKETS_AGENTS_LOADED);

export function loadAgent(agent_id) {
  return (dispatch) => {
    DpApi.sendGet('DP_API/people/' + agent_id).then(
      values => {
        dispatch(agentLoaded(values.getData()));
      }
    );
  }
}
