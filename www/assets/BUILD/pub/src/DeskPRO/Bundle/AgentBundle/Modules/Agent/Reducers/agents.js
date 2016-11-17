import { createReducer } from 'Ampliflux';
import { setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/agentActions';

const initialState = {
  onlineAgents: []
};

export default createReducer(initialState, {
  [actions.setOnlineAgents]: setFullPayload('onlineAgents')
});
