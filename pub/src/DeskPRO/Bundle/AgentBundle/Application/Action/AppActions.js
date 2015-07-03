import { createAction } from "redux-fsa";

import DpApi from "DeskPRO/Bundle/AgentBundle/Application/Service/DpApi";

export const loadUser = createAction('APP_LOAD_USER', () => {
	return { person_id: 132 };
});