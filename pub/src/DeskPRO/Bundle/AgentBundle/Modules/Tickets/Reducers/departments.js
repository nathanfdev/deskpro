import Immutable from "immutable";
import { createReducer } from "Ampliflux";

import * as departmentActions from "../Actions/DepartmentsActions";

export default createReducer(r => {
	r.initialState = Immutable.Map({
    isLoaded: false,
    departments: []
  });

  r.handleProperty(departmentActions.setDepartments, 'departments');
  r.handleAsyncWithStatus(departmentActions.loadDepartments);
});
