import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/uiActions';

const initialState = {
  overlayShown: false,
  chating: false
};

export default createReducer(initialState, {
  [actions.toggleOverlay]: (state) => {
    return state.set('overlayShown', !state.get('overlayShown'));
  },
  [actions.openChat]: (state) => {
    return state.mergeIn([], {'overlayShown': false, 'chating': true});
  },
  [actions.closeChat]: (state) => {
    return state.set('chating', false);
  }
});
