import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskFrameList extends Reducer {
  getInitialState() {
    return {
      taskFrameList: null
    };
  }
  
  tasksLoaded(state, action) {
    return {
      ...state,
      taskFrameList: action.payload.data
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTaskList, this.tasksLoaded)
  }
}
