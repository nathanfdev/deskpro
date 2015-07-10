import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Service/DpApi";
import ActionTypes from "./ActionTypes";

export const setAppUser  = createAction(ActionTypes.APP_SET_USER);
export const setIsLoaded = createAction(ActionTypes.APP_IS_LOADED);

export const loadWindow = createAction(ActionTypes.APP_LOAD_WINDOW, () => {
	return new Promise((resolve, reject) => {
		let promises = [];

		// can wait on multiple loads here by adding new
		// promises to the array
		promises.push(new Promise((r) => r())); //0

		Promise.all(promises).then((values) => {
			console.log("XX")
			//dispatch(setAppUser(values[0]));
			//dispatch(setIsLoaded());
			resolve(dispatch => {
				console.log("YYY")
			});
		});
	});
});