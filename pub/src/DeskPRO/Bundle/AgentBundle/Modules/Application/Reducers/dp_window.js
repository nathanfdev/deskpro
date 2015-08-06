import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";

export default class dp_window extends Reducer {
	getInitialState() {
		return {
			isLoaded: false,
			activeAppId: 'tickets',
			collapsedNav: false,
		};
	}

	appHasLoaded(state, action) {
		return {
			...state,
			isLoaded: true
		};
	}

	setActiveApp(state, action) {
		return {
			...state,
			activeAppId: action.payload
		};
	}

	collapseNav(state, action) {
		console.log("PROUT");
		return {
			...state,
			collapseNav: true
		};
	}

	expandNav(state, action) {
		console.log("POUET");
		return {
			...state,
			collapseNav: false
		};
	}

	registerHandlers() {this
		.r(ActionTypes.APP_IS_LOADED, this.appHasLoaded)
		.r(ActionTypes.SET_ACTIVE_APP, this.setActiveApp)
		.r(ActionTypes.COLLAPSE_NAV, this.collapseNav)
		.r(ActionTypes.EXPAND_NAV, this.expandNav)
	}
}
