import ActionTypes from "../Action/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	id: null
};

const r = handleActions({
	[ActionTypes.APP_SET_USER]: (state, action) => action,
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}