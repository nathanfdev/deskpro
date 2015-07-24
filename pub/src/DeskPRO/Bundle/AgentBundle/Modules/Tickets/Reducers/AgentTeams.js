import * as AgentTeamActions from "../Actions/AgentTeamActions";
import { Reducer } from "Ampliflux/reducers";

export default class AgentTeams extends Reducer {
  registerHandlers() {this
    .r(AgentTeamActions.loadAgentTeam, this.agentsTeamLoaded)
    ;
  }
  
  agentsTeamLoaded(state, action) {
    if(!action.payload || !action.payload.data) {
      return state;
    }
    return {
      ...state,
      [action.payload.data.id]: action.payload.data
    };
  }
}
