import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import * as actions from '../Actions/routingActions';
import { stateToString, stateFromString } from '../Service/routing';

const initialState = {
  hash: {}
};

export default createReducer(initialState, {
  [actions.hashChanged]: (state, payload) => {
    if (!payload || payload.length <= 1) {
      return state.set('hash', Immutable.fromJS({}));
    }

    const newHashState = stateFromString(payload.substring(1));
    let next           = state;

    // When we update window.location.hash from updateRoutingState() action reducer and
    // window.onhashchange() event is fired, we don't actually need to update state
    // as state and hash are already in sync.
    //
    // So to prevent unnecessary re-rendering of components using hash state, we skip
    // updating when state and hash are already synchronized.
    //
    // @todo Test this to work correctly when hash state is more extensively used (check re-renderings w/ and w/o this)
    // @todo Think about optimization, ideally comparing hash strings, not state objects
    //
    if (!Immutable.is(state.get('hash'), newHashState)) {
      next = next.merge({ hash: payload ? newHashState : {} });
    }

    return next;
  },

  [actions.updateRoutingState]: (state, { component, option, value }) => {
    let next = state;

    if (!next.hasIn(['hash', component])) {
      next = next.setIn(['hash', component], Immutable.Map());
    }
    next = next.setIn(['hash', component], Immutable.fromJS({ [option]: value }));

    window.location.hash = stateToString(next.get('hash'));

    return next;
  }
});
