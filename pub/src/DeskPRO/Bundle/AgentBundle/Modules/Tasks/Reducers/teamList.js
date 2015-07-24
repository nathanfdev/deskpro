import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TeamList extends Reducer {
  getInitialState() {
    return {
      teamList: null,
      teamCount: 0
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTeams, this.teamsLoaded)
  }
  
  teamsLoaded(state, action) {
    return {
      ...state,
      teamList: action.payload.data,
      teamCount: action.payload.meta.total_count
    };
  }
}
