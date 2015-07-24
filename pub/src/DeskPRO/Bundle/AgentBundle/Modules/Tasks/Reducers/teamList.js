import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
    teamList: null,
    teamCount: 0
};

const r = handleActions({
    [ActionTypes.TASKS_LOAD_TEAMS]: (state, action) => ({
        ...state,
        teamList: action.payload.data,
        teamCount: action.payload.meta.total_count
    })
}, initialState);

export default (state, action = {type: null}) => {
    return r(state, action);
}
