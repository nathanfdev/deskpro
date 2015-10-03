import ActionTypes from "../Actions/ActionTypes";
import { createReducer } from "Ampliflux";

const initialState = {
  router: null
};

export default createReducer(initialState, {
  [ActionTypes.TRANSITION_TO]: (state, payload) => {
    if (state.router) {
      state.router.transitionTo.apply(null, payload);
    }

    return state;
  },
  [ActionTypes.ROUTING_STARTED]: (state, payload) => {
    return state.merge({router: payload});
  }
});
