import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const departmentLoaded = createAction(ActionTypes.TICKETS_DEPARTMENT_LOADED);

export function loadDepartment(dept_id) {
  return (dispatch) => {
    DpApi.sendGet('DP_API/departments/' + dept_id).then(
      values => {
        dispatch(departmentLoaded(values.getData()));
      }
    );
  }
}
