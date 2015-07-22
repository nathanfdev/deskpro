import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const peopleLoaded = createAction(ActionTypes.TICKETS_PEOPLE_LOADED);

export function loadPeople(people_id) {
  return (dispatch) => {
    DpApi.sendGet('DP_API/people/' + people_id).then(
      values => {
        dispatch(peopleLoaded(values.getData()));
      }
    );
  }
}
