import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/agentActions';

const initialState = {
  onlineAgents:         [],
  onlineUserChatAgents: []
};

export default createReducer(initialState, {
  [actions.setOnlineAgents]:         setFullPayload('onlineAgents'),
  [actions.setOnlineUserChatAgents]: setFullPayload('onlineUserChatAgents'),
  [actions.updateAgentStatus]:       (state, payload) => state.set('onlineUserChatAgents', Immutable.fromJS(payload.agent_ids.map(e => parseInt(e, 10)))),
  [actions.toggleUserChat]:          (state, payload) => {
    let onlineAgents = state.get('onlineUserChatAgents');

    const agentId = payload.me.get('id');
    const contains = onlineAgents.contains(agentId);

    if (payload.enabled && !contains) {
      onlineAgents = onlineAgents.push(agentId);
    } else if (!payload.enabled && contains) {
      onlineAgents = onlineAgents.delete(onlineAgents.indexOf(agentId));
    }

    return state.set('onlineUserChatAgents', onlineAgents);
  }
});
