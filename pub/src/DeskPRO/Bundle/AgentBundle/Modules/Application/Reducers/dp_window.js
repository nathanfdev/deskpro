import * as AppActions from "../Actions/AppActions";
import { createReducer } from "Ampliflux";

export default createReducer(r => {
	r.initialState({
		isLoaded: false,
		collapsedNav: false,
		expandedSwitcher: false,
	});

	r.simpleSetAction(AppActions.setIsLoaded, 'isLoaded', true);

	r.asyncAction(AppActions.loadWindow, aa => {
		aa.success((state, data) => {
			return {
				...state,
				isLoaded: true
			}
		})
	});
});
