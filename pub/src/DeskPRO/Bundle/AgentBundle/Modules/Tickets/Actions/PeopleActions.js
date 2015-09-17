import { createAction } from "Ampliflux/actions";
import * as People from "DeskPRO/Bundle/AgentBundle/Services/Api/People";

export const loadPeople = createAction(
  "TICKETS_PEOPLE_LOADED",
  (trigger, people_id) => {
      People.loadPerson(people_id).then(
          values => trigger(values.getData())
      );
  }
)
