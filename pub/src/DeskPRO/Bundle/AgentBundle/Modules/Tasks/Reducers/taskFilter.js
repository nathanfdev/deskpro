import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class taskFilter extends Reducer {
  getInitialState() {
    return {
      taskFilter: null
    };
  }

  setFilter(state, action) {
    return {
      ...state,
      taskFilter: action.payload
    };
  }

  registerHandlers() {this
    .r(TaskListActions.setFilter, this.setFilter)
  }
}
