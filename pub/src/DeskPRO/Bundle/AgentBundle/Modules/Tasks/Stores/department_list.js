import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
    departmentList: null,
    departmentCount: 0
};

const r = handleActions({
    [ActionTypes.TASKS_LOAD_DEPARTMENTS]: (state, action) => ({
        ...state,
        departmentList: action.payload.data,
        departmentCount: action.payload.meta.total_count
    })
}, initialState);

export default (state, action = {type: null}) => {
    return r(state, action);
}
