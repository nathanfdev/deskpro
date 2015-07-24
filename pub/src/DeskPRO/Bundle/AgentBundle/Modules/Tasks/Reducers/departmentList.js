import * as TaskListActions from "../Actions/TaskListActions";
import { Reducer } from "Ampliflux/reducers";

export default class DepartmentList extends Reducer {
  getInitialState() {
    return {
      departmentList: null,
      departmentCount: 0
    };
  }
  
  departmentsLoaded(state, action) {
    return {
      ...state,
      departmentList: action.payload.data,
      departmentCount: action.payload.meta.total_count
    };
  }
  
  registerHandlers() {this
    .r(TaskListActions.loadDepartments, this.departmentsLoaded)
  }
}
