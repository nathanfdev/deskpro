import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";

export default class dp_window extends Reducer {
	getInitialState() {
		return {
			isLoaded: false,
			activeAppId: 'tickets',
			collapsedNav: false,
			expandedSwitcher: false,
      taskView: 'list'
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
		return {
			...state,
			collapseNav: true
		};
	}

	expandNav(state, action) {
		return {
			...state,
			collapseNav: false
		};
	}

	expandSwitcher(state, action) {
		return {
			...state,
			expandedSwitcher: true
		};
	}

	collapseSwitcher(state, action) {
		return {
			...state,
			expandedSwitcher: false
		};
	}

	toggleView(state, action) {
		const taskView = action.payload;

		return {
			...state,
			taskView: taskView
		}
	}

	registerHandlers() {this
		.r(ActionTypes.APP_IS_LOADED, this.appHasLoaded)
		.r(ActionTypes.SET_ACTIVE_APP, this.setActiveApp)
		.r(ActionTypes.COLLAPSE_NAV, this.collapseNav)
		.r(ActionTypes.EXPAND_NAV, this.expandNav)
		.r(ActionTypes.EXPAND_SWITCHER, this.expandSwitcher)
		.r(ActionTypes.COLLAPSE_SWITCHER, this.collapseSwitcher)
    .r(ActionTypes.TOGGLE_VIEW, this.toggleView)
	}
}
