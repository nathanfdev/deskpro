import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
    createdProject: null,
    failedProject: null
};

const r = handleActions({
    [ActionTypes.TASKS_POST_PROJECT]: (state, action) => ({
        ...state,
        createdProject: action.payload.data
    }),
    [ActionTypes.TASKS_POST_PROJECT_FAIL]: (state, action) => {
    console.log('failed');
return {
        ...state,
        failedProject: action.payload.data
    }}
}, initialState);

export default (state, action = {type: null}) => {
    return r(state, action);
}
