import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class ProjectCreate extends Reducer {
  getInitialState() {
    return {
      createdProject: null,
      failedProject: null
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.createProject, this.setPayload('createdProject', 'data'))
    .r(TaskListActions.failedProject, this.saveProjectFailed)
  }
  
  saveProjectFailed(state, action) {
    console.log('failed');
    return {
      ...state,
      failedProject: action.payload.data
    };
  }
}
