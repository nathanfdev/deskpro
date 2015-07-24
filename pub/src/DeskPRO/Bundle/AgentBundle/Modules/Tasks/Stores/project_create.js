import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
    createdProject: null,
    failedProject: null
};

const r = handleActions({
    [ActionTypes.TASKS_POST_PROJECT]: (state, action) => {
        console.log('succeeded');
        return {
        ...state,
            createdProject: action.payload
        }
    },
    [ActionTypes.TASKS_POST_PROJECT_FAIL]: (state, action) => ({
        ...state,
        failedProject: action.payload
    })
}, initialState);

export default (state, action = {type: null}) => {
    return r(state, action);
}
