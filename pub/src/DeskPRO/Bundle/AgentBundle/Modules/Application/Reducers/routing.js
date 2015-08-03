import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	router: null,
};

const r = handleActions({
  [ActionTypes.TRANSITION_TO]: (state, action) => {
    console.log(action, state);
    if(state.router) {
      state.router.transitionTo.apply(null, action.payload);
    }
    return state;
  },
	[ActionTypes.ROUTING_STARTED]: (state, action) => {
    return {
  		...state,
  		router: action.payload
    };
	}
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
