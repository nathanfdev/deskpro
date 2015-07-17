import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	tickets_list: []
};

const r = handleActions({
  [ActionTypes.LOAD_TICKETS]: (state, action) => ({
    ...state,
    tickets_list: action.payload.data
  }),
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
