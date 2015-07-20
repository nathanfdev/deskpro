import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	filter_sets_list: [],
  filter_sets_counts: [],
};

const r = handleActions({
  [ActionTypes.TICKETS_LOAD_FILTER_SETS]: (state, action) => ({
    ...state,
    filter_sets_list: action.payload.data
  }),
  [ActionTypes.TICKETS_LOAD_FILTER_COUNTS]: (state, action) => ({
    ...state,
    filter_sets_counts: action.payload.data
  }),
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
