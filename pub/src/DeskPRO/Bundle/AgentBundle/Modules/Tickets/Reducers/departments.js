import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {};

const r = handleActions({
  [ActionTypes.TICKETS_DEPARTMENT_LOADED]: (state, action) => {
    if(!action.payload || !action.payload.data) {
      return state;
    }
    return {
      ...state,
      [action.payload.data.id]: action.payload.data
    };
  },
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
