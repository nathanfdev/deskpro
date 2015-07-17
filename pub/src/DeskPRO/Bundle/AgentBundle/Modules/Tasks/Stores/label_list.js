import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	labelList: null
};

const r = handleActions({
	[ActionTypes.LOAD_LABELS]: (state, action) => ({
        ...state,
        labelList: action.payload.data
    })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
