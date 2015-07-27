import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class CreatedProject extends Reducer {
  getInitialState() {
    return {
      createdProject: null,
      failedProject: null
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.createProject, this.setPayload('createdProject'))
    .r(TaskListActions.failedProject, this.saveProjectFailed)
  }
  
  saveProjectFailed(state, action) {
    console.log('failed');
    return {
      ...state,
      failedProject: action.payload
    };
  }
}
