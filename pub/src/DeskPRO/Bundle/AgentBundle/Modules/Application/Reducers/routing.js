import * as actions from '../Actions/routingActions';
import { createReducer } from 'Ampliflux';
import { stateToString, stateFromString } from '../Service/hash';
import Immutable from 'immutable';
import shallowEqual from 'fbjs/lib/shallowEqual';

const initialState = {
  hash: {}
};

export default createReducer(initialState, {
  [actions.hashChanged]: (state, payload) => {
    const newHashState = stateFromString(payload.substring(1));

    // When we update window.location.hash from updateHashState() action reducer and
    // window.onhashchange() event is fired, we don't actually need to update state
    // as state and hash are already in sync.
    //
    // So to prevent unnecessary re-rendering of components using hash state, we skip
    // updating when state and hash are already synchronized.
    //
    // @todo Test this all to work correctly when hash state is more extensively used
    //
    if (shallowEqual(state.get('hash').toJS(), newHashState.toJS())) {
      console.error('Skipping update!');
      return state;
    } else {
      console.error('Updating');
      return state.merge({
        hash: payload ? newHashState : null
      });
    }
  },
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
