import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/imChatsActions';
import Immutable from 'immutable';

const initialState = {
  agentChats: [],
};

export default createReducer(initialState, {
  [actions.findChat]: (state, payload) => {
    let agentChats = state.get('agentChats');
    agentChats.push(payload);
    return state.set('agentChats', agentChats);
  }
});