import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
  open: false,
  show_mode: 'filter_group',
  payload: null,
};

const r = handleActions({
  [ActionTypes.TICKETS_SIDEBAR_HOVER_FILTER_GROUPING_OPTIONS]: (state, action) => ({
    ...state,
    open: !state.open,
    payload: action.payload,
  }),
  [ActionTypes.TICKETS_SIDEBAR_HOVER_HIDE]: (state, action) => {
    return {
      ...state,
      open: false,
    }
  },
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
