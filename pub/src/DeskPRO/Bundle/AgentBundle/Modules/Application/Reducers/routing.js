import * as actions from '../Actions/routingActions';
import { createReducer } from 'Ampliflux';
import { stateToString, stateFromString } from '../Service/hash';
import Immutable from 'immutable';

const initialState = {
  hash: null
};

export default createReducer(initialState, {
  [actions.hashChanged]: (state, payload) => state.merge({
    hash: payload ? stateFromString(payload.substring(1)) : null
  }),
  [actions.updateHashState]: (state, {component, option, value}) => {
    let next = state;

    if (!next.hasIn(['hash', component])) {
      next = next.setIn(['hash'], Immutable.fromJS({[component]: {}}));
    }
    next = next.setIn(['hash', component, option], value);

    window.location.hash = stateToString(next);

    return next;
  }
});
