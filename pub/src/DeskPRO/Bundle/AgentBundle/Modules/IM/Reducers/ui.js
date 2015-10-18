import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/uiActions';

const initialState = {
  overlayShown: false
};

export default createReducer(initialState, {
  [actions.toggleOverlay]: (state) => {
    return state.set('overlayShown', !state.get('overlayShown'));
  },
  [actions.toggleChat]: (state) => {
    return state.mergeIn([], {'overlayShown': false, 'chating': !state.get('chating')});
  },
  [actions.closeChat]: (state) => {
    return state.set('chating', false);
  }
});
