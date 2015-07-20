import ActionTypes from "../Actions/ActionTypes";
import { handleActions } from "redux-actions";

const initialState = {
	sidebar_hover: {
    open: false,
    show_mode: 'filter_group',
  }
};

const r = handleActions({
  [ActionTypes.TICKETS_SIDEBAR_HOVER_FILTER_GROUPING_OPTIONS]: (state, action) => {
    let sidebar_state = state.sidebar_hover;
    sidebar_state.open = !sidebar_state.open;
    
    return {
      ...state,
      sidebar_hover: sidebar_state,
    };
  }
}, initialState);

export default (state, action = {type: null}) => {
	return r(state, action);
}
