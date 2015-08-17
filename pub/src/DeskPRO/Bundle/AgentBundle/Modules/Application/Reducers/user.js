import * as AppActions from "../Actions/AppActions";
import { createReducer } from "Ampliflux";

export default createReducer(r => {
	r.initialState({
		id: null
	});

	r.handleProperty(AppActions.setAppUser, "user", "user");
});
