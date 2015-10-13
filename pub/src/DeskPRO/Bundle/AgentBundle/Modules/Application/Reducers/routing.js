import * as actions from '../Actions/routingActions';
import { createReducer } from 'Ampliflux';
import { stateToString, stateFromString } from '../Service/hash';

const initialState = {
  hash: null
};

export default createReducer(initialState, {
  [actions.hashChanged]: (state, payload) => state.merge({
    hash: payload ? stateFromString(payload.substring(1)) : null
  })
});
