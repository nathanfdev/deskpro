import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class ProjectList extends Reducer {
  getInitialState() {
    return {
    	projectList: null,
      projectCount: 0
    };
  }
  
  projectsLoaded(state, action) {
    return {
      ...state,
      projectList: action.payload.data,
      projectCount: action.payload.meta.total_count
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadProjects, this.projectsLoaded)
  }
}
