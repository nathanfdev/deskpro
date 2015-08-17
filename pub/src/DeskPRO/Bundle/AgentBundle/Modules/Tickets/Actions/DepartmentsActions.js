import { createAction } from "Ampliflux";
import * as Departments from "DeskPRO/Bundle/AgentBundle/Services/Api/Departments";

export const setDepartments  = createAction("TICKETS_SET_DEPARTMENTS");
export const loadDepartments = createAction("TICKETS_LOAD_DEPARTMENTS", () => {
  return Departments.loadDepartments().then(httpResult => setDepartments(httpResult.getData()));
});
