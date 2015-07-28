import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskCreate extends Reducer {
  getInitialState() {
    return {
      createdTask: null,
      failedTask: null
    };
  }

  taskCreated(state, action) {
    return {
      ...state,
      createdTask: action.payload
    };
  }

  taskFailed(state, action) {
    return {
      ...state,
      failedTask: action.payload
    };
  }

  registerHandlers() {this
    .r(TaskListActions.createTask, this.taskCreated)
    .r(TaskListActions.failedTask, this.taskFailed)
  }
}
