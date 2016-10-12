import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatsActions';


const initialState = {
  current:        {},
  overlayShown:   false,
  chating:        false,
  groupCreation:  false,
  manuallyClosed: {}
};

export default createReducer(initialState, {
  [actions.startChat]: async({
    success: (state, payload) => state.set('current', Immutable.fromJS(payload))
  }),
  [actions.toggleOverlay]:            state => state.mergeIn([], { overlayShown: !state.get('overlayShown'), groupCreation: false }),
  [actions.openChat]:                 state => state.mergeIn([], { overlayShown: false, chating: true }),
  [actions.closeChat]:                state => state.merge({ chating: false, current: Immutable.fromJS({}) }),
  [actions.toggleGroupDrawer]:        state => state.set('groupCreation', !state.get('groupCreation')),
  [actions.openGroupDrawer]:          state => state.mergeIn([], { overlayShown: false, chating: false, groupCreation: true }),
  [actions.closeGroupDrawer]:         state => state.set('groupCreation', false),
  [actions.markChatAsManuallyClosed]: (state, payload) => state.setIn(['manuallyClosed', payload], true)
});
