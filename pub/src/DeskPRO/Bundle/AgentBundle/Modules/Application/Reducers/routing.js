import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";

export default class routing extends Reducer {
	getInitialState() {
		return {
			router: null,
		};
	}

	transitionTo(state, action) {
		if(state.router) {
	      state.router.transitionTo.apply(null, action.payload);
	    }
	    return state;
	}

	routingStarted(state, action) {
		return {
	  		...state,
	  		router: action.payload
	    };
	}

	registerHandlers() {this
		.r(ActionTypes.TRANSITION_TO, this.transitionTo)
		.r(ActionTypes.ROUTING_STARTED, this.routingStarted)
	}
}
