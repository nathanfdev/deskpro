import ActionTypes from "../Action/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	isLoaded: false
};

const r = handleActions({
	[ActionTypes.APP_IS_LOADED]: (state, action) => ({
		...action,
		isLoaded: true
	})
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}