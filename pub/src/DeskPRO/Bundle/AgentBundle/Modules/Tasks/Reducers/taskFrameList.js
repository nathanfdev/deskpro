import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskFrameList extends Reducer {
  getInitialState() {
    return {
      taskFrameList: null,
      taskFrameSource: null
    };
  }
  
  tasksLoaded(state, action) {
    return {
      ...state,
      taskFrameList: action.payload.data,
      taskFrameSource: action.payload.source
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadTaskList, this.tasksLoaded)
  }
}
