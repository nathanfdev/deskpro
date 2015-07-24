import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	taskList: null,
    taskCount: 0,
    myTaskCount: 0,
    teamTaskCount: 0,
    deptTaskCount: 0,
    delegatedTaskCount: 0,
    unassignedTaskCount: 0
};

const r = handleActions({
	[ActionTypes.TASKS_LOAD_TASKS]: (state, action) => ({
        ...state,
        taskList: action.payload.data,
        taskCount: action.payload.meta.total_count
    }),
    [ActionTypes.TASKS_LOAD_MY_TASKS]: (state, action) => ({
        ...state,
        myTaskCount: action.payload.meta.total_count
    }),
    [ActionTypes.TASKS_LOAD_TEAM_TASKS]: (state, action) => ({
        ...state,
        teamTaskCount: action.payload.meta.total_count
    }),
    [ActionTypes.TASKS_LOAD_DEPARTMENT_TASKS]: (state, action) => ({
        ...state,
        deptTaskCount: action.payload.meta.total_count
    }),
    [ActionTypes.TASKS_LOAD_DELEGATED_TASKS]: (state, action) => ({
        ...state,
        delegatedTaskCount: action.payload.meta.total_count
    }),
    [ActionTypes.TASKS_LOAD_UNASSIGNED_TASKS]: (state, action) => ({
        ...state,
        unassignedTaskCount: action.payload.meta.total_count
    })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
