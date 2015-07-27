import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class ProjectCreate extends Reducer {
  getInitialState() {
    return {
      createdProject: null,
      failedProject: null,
    };
  }

  projectCreated(state, action) {
    return {
      ...state,
      createdProject: action.payload
    };
  }

  projectFailed(state, action) {
    return {
      ...state,
      failedProject: action.payload
    };
  }

  registerHandlers() {this
    .r(TaskListActions.createProject, this.projectCreated)
    .r(TaskListActions.failedProject, this.projectFailed)
  }
}
