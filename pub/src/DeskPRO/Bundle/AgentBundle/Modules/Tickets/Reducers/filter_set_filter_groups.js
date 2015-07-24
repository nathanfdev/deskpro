import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
  filter_set_filter_groups: {
    filter_id: 0,
    grouping: null,
    data: []
  },
};

const r = handleActions({
  [ActionTypes.TICKETS_LOAD_FILTER_GROUPS]: (state, action) => ({
    ...state,
    filter_set_filter_groups: action.payload
  })
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
