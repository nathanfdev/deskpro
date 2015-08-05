import { createAction } from "Ampliflux/actions";
import * as Departments from "DeskPRO/Bundle/AgentBundle/Services/Api/Departments";

export const loadDepartment = createAction(
  "TICKETS_DEPARTMENT_LOADED",
  (trigger, dept_id) => {
      Departments.loadDepartment(dept_id).then(
          (values) => trigger(values.getData())
      );
  }
);
