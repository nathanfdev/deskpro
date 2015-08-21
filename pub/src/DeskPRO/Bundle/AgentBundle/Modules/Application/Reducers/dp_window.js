import ActionTypes from "../Actions/ActionTypes";
import { Reducer } from "Ampliflux/reducers";

export default class dp_window extends Reducer {
	getInitialState() {
		return {
			isLoaded: false,
			activeAppId: 'tickets',
			collapsedNav: false,
			expandedSwitcher: false,
      kanbanOpen: false,
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

	toggleKanban(state, action) {
    console.log(state.kanbanOpen);
		return {
			...state,
			kanbanOpen: !state.kanbanOpen
		}
	}

	registerHandlers() {this
		.r(ActionTypes.APP_IS_LOADED, this.appHasLoaded)
		.r(ActionTypes.SET_ACTIVE_APP, this.setActiveApp)
		.r(ActionTypes.COLLAPSE_NAV, this.collapseNav)
		.r(ActionTypes.EXPAND_NAV, this.expandNav)
		.r(ActionTypes.EXPAND_SWITCHER, this.expandSwitcher)
		.r(ActionTypes.COLLAPSE_SWITCHER, this.collapseSwitcher)
    .r(ActionTypes.TOGGLE_KANBAN, this.toggleKanban)
	}
}
