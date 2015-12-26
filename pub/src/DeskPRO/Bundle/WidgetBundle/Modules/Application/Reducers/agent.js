import { createReducer } from 'Ampliflux';
import { setFullPayload, async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/agentActions';

const initialState = {
  agents: []
};

export default createReducer(initialState, {
  [actions.loadOnlineAgents]: async({
    done: setFullPayload('agents')
  })
});
