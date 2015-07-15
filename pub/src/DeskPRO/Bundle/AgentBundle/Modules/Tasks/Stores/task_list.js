import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	//TODO
};

const r = handleActions({
	//TODO
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
