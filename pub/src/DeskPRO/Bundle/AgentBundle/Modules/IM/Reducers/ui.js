import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/uiActions';

const initialState = {
  overlayShown: false,
  chating: false,
  current: {}
};

export default createReducer(initialState, {
  [actions.toggleOverlay]: (state) => {
    return state.set('overlayShown', !state.get('overlayShown'));
  },
  [actions.openChat]: (state, payload) => {
    return state.mergeIn([], {'overlayShown': false, 'chating': true, current: payload});
  },
  [actions.closeChat]: (state) => {
    return state.set('chating', false);
  }
});
