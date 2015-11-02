import { createReducer } from 'Ampliflux';
import * as TaskListActions from '../Actions/TaskListActions';

const initialState = {
  agentCount: 0,
  agentList: []
};

export default createReducer(initialState, {
  [TaskListActions.loadAgents]: (state, payload) => {
    const agentCount = payload.meta ? payload.meta.total_count : 0;

    const withCount = state.set('agentCount', agentCount);
    return withCount.set('agentList', payload.data);
  }
});
