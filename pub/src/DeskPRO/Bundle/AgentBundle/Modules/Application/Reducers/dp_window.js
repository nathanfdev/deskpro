import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	isLoaded: false,
	activeAppId: 'tickets'
};

const r = handleActions({
	[ActionTypes.APP_IS_LOADED]: (state, action) => ({
		...state,
		isLoaded: true
	}),
	[ActionTypes.SET_ACTIVE_APP]: (state, action) => ({
		...state,
		activeAppId: action.payload
	})
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
