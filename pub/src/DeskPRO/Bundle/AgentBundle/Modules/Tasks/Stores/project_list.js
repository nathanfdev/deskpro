import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	projectList: null,
    projectCount: 0
};

const r = handleActions({
	[ActionTypes.LOAD_PROJECTS]: (state, action) => ({
        ...state,
        projectList: action.payload.data,
        projectCount: action.payload.meta.total_count
    })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
