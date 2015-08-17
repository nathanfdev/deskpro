import Immutable from "immutable";
import * as AppActions from "../Actions/AppActions";
import { createReducer } from "Ampliflux";

export default createReducer(r => {
	r.initialState(Immutable.Map({
		isLoaded: false,
		collapsedNav: false,
		expandedSwitcher: false,
	}));

	r.handle(AppActions.setIsLoaded, () => {isLoaded : true});

	r.handleAsync(AppActions.loadWindow, aa => {
		aa.handleSuccess((state, data) => {
			return {
				...state,
				isLoaded: true
			}
		})
	});
});
