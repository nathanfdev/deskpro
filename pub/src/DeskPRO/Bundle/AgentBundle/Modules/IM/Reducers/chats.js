import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatsActions';

const initialState = {
  current: {},
  overlayShown: false,
  chating: false,
  currentUI: {},
  manuallyClosed: {}
};

export default createReducer(initialState, {
  [actions.startChat]: (state, payload) => {
    if(payload) {
      return state.set('current', payload);
    } else {
      return state;
    }
  },
  [actions.toggleOverlay]: (state) => {
    return state.set('overlayShown', !state.get('overlayShown'));
  },
  [actions.openChat]: (state) => {
    return state.mergeIn([], {'overlayShown': false, 'chating': true});
  },
  [actions.closeChat]: (state) => {
    return state.set('chating', false);
  },
  [actions.markChatAsManuallyClosed]: (state, payload) => {
    return state.setIn(['manuallyClosed', payload], true);
  }
});
