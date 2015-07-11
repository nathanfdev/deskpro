import { createAction } from "redux-actions";

import DpApi from "DeskPRO/Bundle/AgentBundle/Services/DpApi";
import ActionTypes from "./ActionTypes";

export const setAppUser  = createAction(ActionTypes.APP_SET_USER);
export const setIsLoaded = createAction(ActionTypes.APP_IS_LOADED);

export const loadWindow = () => {
	return dispatch => {
		let promises = [];

		// can wait on multiple loads here by adding new
		// promises to the array
		promises.push(DpApi.sendGet('DP_API/me')); //0

		Promise.all(promises).then((values) => {
			dispatch(setAppUser(values[0].getData().data.person));
			dispatch(setIsLoaded());
		});
	};
};