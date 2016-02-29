import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatsActions';

const initialState = {
  current: {},
  overlayShown: false,
  chating: false,
  manuallyClosed: {}
};

export default createReducer(initialState, {
  [actions.startChat]: async({success: (state, payload) => state.set('current', payload)}),
  [actions.toggleOverlay]: (state) => {
    return state.set('overlayShown', !state.get('overlayShown'));
  },
  [actions.openChat]: (state) => {
    return state.mergeIn([], {'overlayShown': false, 'chating': true});
  },
  [actions.closeChat]: (state) => {
    return state.merge({'chating': false, 'current': {}});
  },
  [actions.markChatAsManuallyClosed]: (state, payload) => {
    return state.setIn(['manuallyClosed', payload], true);
  }
});
