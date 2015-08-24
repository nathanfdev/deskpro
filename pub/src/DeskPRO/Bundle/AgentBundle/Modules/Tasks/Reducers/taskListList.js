import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class TaskListList extends Reducer {
  getInitialState() {
    return {
      taskList: null
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadLists, this.listsLoaded)
  }
  
  listsLoaded(state, action) {
    return {
      ...state,
      taskList: action.payload.data
    };
  }
}
