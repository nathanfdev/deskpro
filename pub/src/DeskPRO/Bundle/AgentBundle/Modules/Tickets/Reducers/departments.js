import * as DepartmentsActions from "../Actions/DepartmentsActions";
import { Reducer } from "Ampliflux/reducers";

export default class Departments extends Reducer {
  departmentLoaded(state, action) {
    if(!action.payload || !action.payload.data) {
      return state;
    }
    return {
      ...state,
      [action.payload.data.id]: action.payload.data
    };
  }
  
  registerHandlers() {this
    .r("TICKETS_DEPARTMENT_LOADED", this.departmentLoaded)
  }
}
