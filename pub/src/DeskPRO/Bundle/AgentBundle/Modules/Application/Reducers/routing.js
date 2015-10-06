import * as actions from '../Actions/AppActions';
import { createReducer } from 'Ampliflux';

const initialState = {
  router: null
};

export default createReducer(initialState, {
  [actions.doTransitionTo]: (state, payload) => {
    if (state.router) {
      state.router.transitionTo.apply(null, payload);
    }

    return state;
  },
  [actions.routingStarted]: (state, payload) => {
    return state.merge({router: payload});
  }
});
