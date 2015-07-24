import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class AgentList extends Reducer {
  getInitialState() {
    return {
      agentList: null,
      agentCount: 0
    };
  }
  
  agentsLoaded(state, action) {
    return {
      ...state,
      agentList: action.payload.data,
      agentCount: action.payload.meta.total_count
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadAgents, this.agentsLoaded)
  }
}
