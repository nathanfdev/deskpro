import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	stars_counts: [],
};

const r = handleActions({
  [ActionTypes.TICKETS_LOAD_TICKET_STAR_COUNTS]: (state, action) => ({
    ...state,
    stars_counts: action.payload.data
  })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
