import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatsActions';


const initialState = {
  current:        {},
  editChat:       Immutable.fromJS({}),
  overlayShown:   false,
  chating:        false,
  groupCreation:  false,
  manuallyClosed: {},
  checkedAgents:  {},
  hiddenChats:    {},
  activeTabs:     {}
};

export default createReducer(initialState, {
  [actions.openChat]:                 state => state.mergeIn([], { overlayShown: false, chating: true }),
  [actions.closeChat]:                state => state.merge({ chating: false, current: Immutable.fromJS({}) }),
  [actions.closeGroupDrawer]:         state => state.mergeIn([], { groupCreation: false, checkedAgents: {}, editChat: Immutable.fromJS({}) }),
  [actions.markChatAsManuallyClosed]: (state, payload) => {
    if (payload) {
      return state.setIn(['manuallyClosed', payload], true);
    }

    return state;
  },
  [actions.toggleGroupDrawer]: state => state.set('groupCreation', !state.get('groupCreation')),

  [actions.startChat]: async({
    success: (state, payload) => state.set('current', Immutable.fromJS(payload))
  }),
  [actions.toggleOverlay]: state => state.mergeIn([], {
    overlayShown:  !state.get('overlayShown'),
    chating:       false,
    groupCreation: false
  }),
  [actions.openGroupDrawer]: (state, payload) => {
    const diff = {
      overlayShown:  false,
      groupCreation: true,
      checkedAgents: {},
      editChat:      payload.editChat || Immutable.fromJS({})
    };
    if (payload.agentIds && Array.isArray(payload.agentIds)) {
      payload.agentIds.forEach((item) => { diff.checkedAgents[item] = true; return null; });
    }
    return state.mergeIn([], diff);
  },
  [actions.hideChat]:       (state, payload) => state.set('hiddenChats', payload),
  [actions.revealChat]:     (state, payload) => state.set('hiddenChats', payload),
  [actions.loadActiveTabs]: (state, payload) => state.set('activeTabs', payload)
});
