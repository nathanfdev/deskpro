import { createAction } from "Ampliflux/actions";
import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";

export const loadDepartment = createAction(
  "TICKETS_DEPARTMENT_LOADED",
  (trigger, dept_id) => {
    DpApi.sendGet('DP_API/departments/' + dept_id).then(
      (values) => trigger(values.getData())
    );
  }
);

