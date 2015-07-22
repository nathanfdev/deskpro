import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {};

const handlePerson = (state, action) => {
  console.log(action.payload);
  if(!action.payload || !action.payload.data) {
    return state;
  }
  return {
    ...state,
    [action.payload.data.id]: action.payload.data
  };
}

const r = handleActions({
  [ActionTypes.TICKETS_AGENTS_LOADED]: handlePerson,
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
