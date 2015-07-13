import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	filter_sets: [],
	labels: [],
	flags: []
};

const r = handleActions({

}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
