import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	filter_sets_list: []
};

const r = handleActions({
  [ActionTypes.LOAD_FILTER_SETS]: (state, action) => ({
    ...state,
    filter_sets_list: action.payload.data
  })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
