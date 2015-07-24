import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	agentList: null,
    agentCount: 0
};

const r = handleActions({
	[ActionTypes.TASKS_LOAD_AGENTS]: (state, action) => ({
        ...state,
        agentList: action.payload.data,
        agentCount: action.payload.meta.total_count
    })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
