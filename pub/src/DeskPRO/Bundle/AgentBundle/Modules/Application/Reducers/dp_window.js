import * as AppActions from "../Actions/AppActions";
import { createReducer } from "Ampliflux";

export default createReducer(r => {
	r.initialState({
		isLoaded: false,
		collapsedNav: false,
		expandedSwitcher: false,
	});

	r.simpleAction(AppActions.setIsLoaded, 'isLoaded');

	r.asyncAction(AppActions.loadWindow, aa => {
		aa.success((state, data) => {
			console.log("XX");
			return {
				...state,
				isLoaded: true
			}
		})
	});
});
